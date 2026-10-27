<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\MaintenanceReport;
use App\Models\PmrRequest;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use App\Models\RoomMove;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FrontOfficeService
{
    /**
     * Check-in guest to room
     */
    public static function checkIn(Reservation $reservation, ?Room $room = null): void
    {
        DB::transaction(function () use ($reservation, $room) {
            if (!in_array($reservation->status, ['confirmed', 'pending'])) {
                throw new InvalidArgumentException("Hanya reservasi terkonfirmasi yang dapat di-check-in.");
            }

            // If room not assigned yet, use provided room
            $existingAllocation = $reservation->allocatedRooms()->first();
            $targetRoom = $room ?? ($existingAllocation ? $existingAllocation->room : null);

            if (!$targetRoom) {
                throw new InvalidArgumentException("Kamar fisik belum ditentukan untuk reservasi ini.");
            }

            // Validate room status
            if ($targetRoom->operational_status !== 'active') {
                throw new InvalidArgumentException("Kamar {$targetRoom->room_number} sedang dalam status {$targetRoom->operational_status} dan tidak dapat digunakan.");
            }

            if (!in_array($targetRoom->cleaning_status, ['clean', 'inspected'])) {
                throw new InvalidArgumentException("Kamar {$targetRoom->room_number} belum bersih/siap (Status: {$targetRoom->cleaning_status}). Silakan prioritaskan pembersihan.");
            }

            // Create allocation if not exist yet
            if (!$existingAllocation) {
                if (!$targetRoom->isAvailable($reservation->check_in_date->toDateString(), $reservation->check_out_date->toDateString(), $reservation->id)) {
                    throw new InvalidArgumentException("Kamar {$targetRoom->room_number} tidak tersedia untuk rentang tanggal reservasi ini.");
                }

                ReservationRoom::create([
                    'reservation_id' => $reservation->id,
                    'room_id' => $targetRoom->id,
                    'assigned_from' => $reservation->check_in_date,
                    'assigned_to' => $reservation->check_out_date,
                    'notes' => 'Alokasi saat check-in',
                ]);
            }

            $reservation->update([
                'status' => 'checked_in',
            ]);

            AuditLog::record('check_in', $reservation, null, ['status' => 'checked_in', 'room' => $targetRoom->room_number], "Check-in tamu {$reservation->customer->name} ke kamar {$targetRoom->room_number}");
        });
    }

    /**
     * Checkout guest: Automatically mark rooms as dirty (PRD 7.8, 11.1)
     */
    public static function checkOut(Reservation $reservation): void
    {
        DB::transaction(function () use ($reservation) {
            if ($reservation->status !== 'checked_in') {
                throw new InvalidArgumentException("Hanya tamu yang sedang menginap (in-house) yang dapat di-checkout.");
            }

            $reservation->update([
                'status' => 'checked_out',
            ]);

            // Automatically set all allocated rooms to 'dirty'
            foreach ($reservation->allocatedRooms as $allocation) {
                $room = $allocation->room;
                $room->update(['cleaning_status' => 'dirty']);
                AuditLog::record('room_dirty_auto', $room, ['cleaning_status' => 'clean'], ['cleaning_status' => 'dirty'], "Kamar otomatis kotor setelah checkout reservasi #{$reservation->reservation_number}");
            }

            AuditLog::record('check_out', $reservation, null, ['status' => 'checked_out'], "Checkout tamu {$reservation->customer->name}");

            // Recognize stay revenue if COA exists (only recognize remaining un-audited nights to prevent double revenue)
            if (\App\Models\ChartOfAccount::count() > 0 && $reservation->total_amount > 0) {
                $alreadyPosted = \App\Models\JournalEntry::where('reference_type', 'room_checkout')
                    ->where('reference_id', $reservation->id)
                    ->exists();

                if (!$alreadyPosted) {
                    $auditedEntries = \App\Models\JournalEntry::where('reference_type', 'night_audit')
                        ->where('reference_id', $reservation->id)
                        ->with('lines.account')
                        ->get();

                    $auditedRoomRev = 0;
                    $auditedTax = 0;
                    foreach ($auditedEntries as $entry) {
                        foreach ($entry->lines as $line) {
                            if ($line->account?->code === '4110') {
                                $auditedRoomRev += (int) $line->credit;
                            } elseif ($line->account?->code === '2120') {
                                $auditedTax += (int) $line->credit;
                            }
                        }
                    }

                    $netRoomRev = max(0, $reservation->subtotal - $reservation->discount_amount);
                    $remainingRoomRev = max(0, $netRoomRev - $auditedRoomRev);
                    $remainingTax = max(0, $reservation->tax_amount - $auditedTax);
                    $serviceCharge = (int) $reservation->service_charge_amount;

                    $remainingToRecognize = $remainingRoomRev + $remainingTax + $serviceCharge;

                    if ($remainingToRecognize > 0) {
                        $lines = [
                            ['account_code' => '2110', 'debit' => $remainingToRecognize, 'credit' => 0, 'memo' => 'Penyelesaian sisa deposit tamu'],
                        ];

                        if ($remainingRoomRev > 0) {
                            $lines[] = ['account_code' => '4110', 'debit' => 0, 'credit' => $remainingRoomRev, 'memo' => 'Sisa Pendapatan Sewa Kamar'];
                        }

                        if ($remainingTax > 0) {
                            $lines[] = ['account_code' => '2120', 'debit' => 0, 'credit' => $remainingTax, 'memo' => 'Utang Pajak Daerah PB1'];
                        }

                        if ($serviceCharge > 0) {
                            $lines[] = ['account_code' => '4140', 'debit' => 0, 'credit' => $serviceCharge, 'memo' => 'Pendapatan Service Charge'];
                        }

                        AccountingService::createBalancedEntry(
                            description: "Pengakuan sisa pendapatan checkout reservasi #{$reservation->reservation_number}",
                            entryDate: now()->toDateString(),
                            lines: $lines,
                            refType: 'room_checkout',
                            refId: $reservation->id
                        );
                    }
                }
            }
        });
    }

    /**
     * Extend stay duration with availability check and pricing recalculation
     */
    public static function extendStay(Reservation $reservation, string $newCheckOutDate, int $manualAdjustment = 0): void
    {
        DB::transaction(function () use ($reservation, $newCheckOutDate, $manualAdjustment) {
            $currentCheckOut = $reservation->check_out_date;
            $newCheckOut = Carbon::parse($newCheckOutDate);

            if ($newCheckOut->lte($currentCheckOut)) {
                throw new InvalidArgumentException("Tanggal perpanjangan harus lebih besar dari tanggal checkout saat ini ({$currentCheckOut->format('d/m/Y')}).");
            }

            // Check availability for additional nights
            $allocatedRoom = $reservation->allocatedRooms()->first()?->room;
            if ($allocatedRoom) {
                if (!$allocatedRoom->isAvailable($currentCheckOut->toDateString(), $newCheckOut->toDateString(), $reservation->id)) {
                    throw new InvalidArgumentException("Kamar {$allocatedRoom->room_number} tidak tersedia untuk malam perpanjangan. Pertimbangkan Room Move.");
                }
            }

            // Calculate cost for additional nights
            $extensionBreakdown = PricingService::calculateStay(
                $reservation->roomType,
                $currentCheckOut->toDateString(),
                $newCheckOut->toDateString(),
                null,
                $manualAdjustment
            );

            $oldTotal = $reservation->total_amount;
            $newSubtotal = $reservation->subtotal + $extensionBreakdown['subtotal'];
            $newTax = $reservation->tax_amount + $extensionBreakdown['tax_amount'];
            $newServiceCharge = $reservation->service_charge_amount + $extensionBreakdown['service_charge_amount'];
            $newTotal = $reservation->total_amount + $extensionBreakdown['total_amount'];

            $reservation->update([
                'check_out_date' => $newCheckOut->toDateString(),
                'subtotal' => $newSubtotal,
                'tax_amount' => $newTax,
                'service_charge_amount' => $newServiceCharge,
                'total_amount' => $newTotal,
                'payment_status' => ($reservation->paid_amount >= $newTotal) ? 'paid' : 'partially_paid',
            ]);

            // Sync active invoice if exists
            $activeInvoice = $reservation->invoices()->latest()->first();
            if ($activeInvoice) {
                $activeInvoice->update([
                    'subtotal' => $newSubtotal,
                    'tax_amount' => $newTax,
                    'service_charge_amount' => $newServiceCharge,
                    'total_amount' => $newTotal,
                    'status' => ($reservation->paid_amount >= $newTotal) ? 'paid' : ($reservation->paid_amount > 0 ? 'partially_paid' : 'unpaid'),
                ]);
            }

            // Update room allocation end date
            if ($allocatedRoom) {
                $allocation = $reservation->allocatedRooms()->where('room_id', $allocatedRoom->id)->first();
                $allocation?->update(['assigned_to' => $newCheckOut->toDateString()]);
            }

            AuditLog::record('extend_stay', $reservation, ['check_out' => $currentCheckOut->toDateString(), 'total' => $oldTotal], ['check_out' => $newCheckOut->toDateString(), 'total' => $reservation->total_amount], "Perpanjang inap s.d {$newCheckOut->format('d/m/Y')}");
        });
    }

    /**
     * Move guest to another room (Room Move)
     */
    public static function roomMove(Reservation $reservation, Room $newRoom, string $reason, int $priceAdjustment = 0): RoomMove
    {
        return DB::transaction(function () use ($reservation, $newRoom, $reason, $priceAdjustment) {
            $currentAllocation = $reservation->allocatedRooms()->first();
            if (!$currentAllocation) {
                throw new InvalidArgumentException("Reservasi ini belum memiliki alokasi kamar awal.");
            }

            $oldRoom = $currentAllocation->room;
            if ($oldRoom->id === $newRoom->id) {
                throw new InvalidArgumentException("Kamar baru harus berbeda dari kamar saat ini.");
            }

            if ($newRoom->operational_status !== 'active') {
                throw new InvalidArgumentException("Kamar tujuan {$newRoom->room_number} tidak aktif.");
            }

            if (!in_array($newRoom->cleaning_status, ['clean', 'inspected'])) {
                throw new InvalidArgumentException("Kamar tujuan {$newRoom->room_number} belum bersih/siap.");
            }

            if (!$newRoom->isAvailable(now()->toDateString(), $reservation->check_out_date->toDateString(), $reservation->id)) {
                throw new InvalidArgumentException("Kamar tujuan {$newRoom->room_number} tidak tersedia untuk sisa malam inap.");
            }

            // Remove old allocation and set old room to dirty
            $currentAllocation->delete();
            $oldRoom->update(['cleaning_status' => 'dirty']);

            // Create new allocation
            ReservationRoom::create([
                'reservation_id' => $reservation->id,
                'room_id' => $newRoom->id,
                'assigned_from' => now()->toDateString(),
                'assigned_to' => $reservation->check_out_date,
                'notes' => "Pindah dari Kamar {$oldRoom->room_number}",
            ]);

            if ($priceAdjustment != 0) {
                $newSubtotal = max(0, $reservation->subtotal + $priceAdjustment);
                $taxPercent = (int) \App\Models\HotelSetting::get('tax_percentage', 10);
                $newTax = (int) round(($newSubtotal - $reservation->discount_amount) * $taxPercent / 100);
                $newTotal = ($newSubtotal - $reservation->discount_amount) + $newTax + $reservation->service_charge_amount;

                $reservation->update([
                    'subtotal' => $newSubtotal,
                    'tax_amount' => $newTax,
                    'total_amount' => $newTotal,
                    'payment_status' => ($reservation->paid_amount >= $newTotal) ? 'paid' : ($reservation->paid_amount > 0 ? 'partially_paid' : 'unpaid'),
                ]);

                // Sync active invoice if exists
                $activeInvoice = $reservation->invoices()->latest()->first();
                if ($activeInvoice) {
                    $activeInvoice->update([
                        'subtotal' => $newSubtotal,
                        'tax_amount' => $newTax,
                        'total_amount' => $newTotal,
                        'status' => ($activeInvoice->paid_amount >= $newTotal) ? 'paid' : ($activeInvoice->paid_amount > 0 ? 'partially_paid' : 'unpaid'),
                    ]);
                }
            }

            // Record room move
            $move = RoomMove::create([
                'reservation_id' => $reservation->id,
                'from_room_id' => $oldRoom->id,
                'to_room_id' => $newRoom->id,
                'reason' => $reason,
                'price_adjustment' => $priceAdjustment,
                'created_by' => auth()->id(),
            ]);

            AuditLog::record('room_move', $reservation, ['room' => $oldRoom->room_number], ['room' => $newRoom->room_number, 'adjustment' => $priceAdjustment], "Pindah kamar dari {$oldRoom->room_number} ke {$newRoom->room_number}. Alasan: {$reason}");

            return $move;
        });
    }

    /**
     * Housekeeping: Update room cleaning status
     */
    public static function updateCleaningStatus(Room $room, string $newStatus): void
    {
        $validStatuses = ['dirty', 'cleaning', 'clean', 'inspected'];
        if (!in_array($newStatus, $validStatuses)) {
            throw new InvalidArgumentException("Status kebersihan tidak valid.");
        }

        $oldStatus = $room->cleaning_status;
        $room->update(['cleaning_status' => $newStatus]);

        // If marked clean or inspected, complete any active PMR requests for this room
        if (in_array($newStatus, ['clean', 'inspected'])) {
            $activePmrs = PmrRequest::where('room_id', $room->id)
                ->where('status', 'requested')
                ->get();
            foreach ($activePmrs as $pmr) {
                self::completePmr($pmr, false);
            }
        }

        AuditLog::record('update_cleaning_status', $room, ['cleaning_status' => $oldStatus], ['cleaning_status' => $newStatus], "Ubah status kebersihan kamar {$room->room_number} menjadi {$newStatus}");
    }

    /**
     * Housekeeping: Request PMR (Please Make Up Room)
     */
    public static function createPmr(Room $room, string $notes, string $priority = 'normal', ?int $reservationId = null): PmrRequest
    {
        $pmr = PmrRequest::create([
            'room_id' => $room->id,
            'reservation_id' => $reservationId,
            'requested_at' => now(),
            'notes' => $notes,
            'priority' => $priority,
            'status' => 'requested',
        ]);

        AuditLog::record('create_pmr', $pmr, null, $pmr->toArray(), "Permintaan PMR untuk kamar {$room->room_number}");
        return $pmr;
    }

    /**
     * Housekeeping: Complete PMR
     */
    public static function completePmr(PmrRequest $pmr, bool $updateRoomClean = true): void
    {
        $pmr->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        if ($updateRoomClean && $pmr->room && in_array($pmr->room->cleaning_status, ['dirty', 'cleaning'])) {
            $oldRoomStatus = $pmr->room->cleaning_status;
            $pmr->room->update(['cleaning_status' => 'clean']);
            AuditLog::record('update_cleaning_status', $pmr->room, ['cleaning_status' => $oldRoomStatus], ['cleaning_status' => 'clean'], "Status kamar {$pmr->room->room_number} otomatis bersih setelah PMR diselesaikan.");
        }

        AuditLog::record('complete_pmr', $pmr, ['status' => 'requested'], ['status' => 'completed'], "PMR Kamar {$pmr->room->room_number} selesai.");
    }

    /**
     * Maintenance: Report damaged room and set operational status to maintenance
     */
    public static function reportMaintenance(Room $room, string $description, string $urgency = 'medium'): MaintenanceReport
    {
        return DB::transaction(function () use ($room, $description, $urgency) {
            $report = MaintenanceReport::create([
                'room_id' => $room->id,
                'reported_by' => auth()->id(),
                'issue_description' => $description,
                'urgency' => $urgency,
                'status' => 'reported',
            ]);

            $room->update(['operational_status' => 'maintenance']);

            AuditLog::record('report_maintenance', $report, null, $report->toArray(), "Kamar {$room->room_number} dilaporkan rusak ({$urgency}): {$description}");
            return $report;
        });
    }

    /**
     * Maintenance: Resolve and restore room to active
     */
    public static function resolveMaintenance(MaintenanceReport $report, ?string $notes = null): void
    {
        DB::transaction(function () use ($report, $notes) {
            $report->update([
                'status' => 'repaired',
                'resolution_notes' => $notes,
                'repaired_at' => now(),
            ]);

            $room = $report->room;
            $room->update([
                'operational_status' => 'active',
                'cleaning_status' => 'dirty', // Needs cleaning before inspection
            ]);

            AuditLog::record('resolve_maintenance', $report, ['status' => 'reported'], ['status' => 'repaired'], "Perbaikan kamar {$room->room_number} selesai. Status kamar aktif (perlu dibersihkan).");
        });
    }
}
