<?php

namespace App\Services;

use App\Models\AdditionalService;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\HotelSetting;
use App\Models\NumberingSequence;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\ReservationServiceItem;
use App\Models\Room;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ReservationService
{
    /**
     * Get available physical room count for a room type within dates.
     * $channel: 'all' (default, evaluates physical room inventory) or 'website' (evaluates both physical room inventory and website quota allotment).
     */
    public static function getAvailableRoomCount(RoomType $roomType, string $checkIn, string $checkOut, string $channel = 'all'): int
    {
        $totalActiveRooms = $roomType->relationLoaded('rooms')
            ? $roomType->rooms->where('operational_status', 'active')->count()
            : $roomType->rooms()->where('operational_status', 'active')->count();

        // Count overlapping reservations that hold inventory
        $checkInDate = \Carbon\Carbon::parse($checkIn)->toDateString();
        $checkOutDate = \Carbon\Carbon::parse($checkOut)->toDateString();

        $bookedRooms = Reservation::where('room_type_id', $roomType->id)
            ->whereNotIn('status', ['cancelled', 'expired'])
            ->where(function ($query) use ($checkInDate, $checkOutDate) {
                $query->whereDate('check_in_date', '<', $checkOutDate)
                      ->whereDate('check_out_date', '>', $checkInDate);
            })
            ->count();

        $physicalAvailable = max(0, $totalActiveRooms - $bookedRooms);

        if ($channel === 'website') {
            $quota = $roomType->website_quota !== null ? (int) $roomType->website_quota : $totalActiveRooms;
            if ($quota <= 0) {
                return 0;
            }

            $bookedWebsite = Reservation::where('room_type_id', $roomType->id)
                ->whereNotIn('status', ['cancelled', 'expired'])
                ->where('source', 'website')
                ->where(function ($query) use ($checkInDate, $checkOutDate) {
                    $query->whereDate('check_in_date', '<', $checkOutDate)
                          ->whereDate('check_out_date', '>', $checkInDate);
                })
                ->count();

            $webQuotaRemaining = max(0, $quota - $bookedWebsite);

            return min($physicalAvailable, $webQuotaRemaining);
        }

        return $physicalAvailable;
    }

    /**
     * Get list of physical rooms that are currently free and clean/ready for allocation
     */
    public static function getAvailableRooms(RoomType $roomType, string $checkIn, string $checkOut): array
    {
        return $roomType->rooms()
            ->where('operational_status', 'active')
            ->get()
            ->filter(fn (Room $room) => $room->isAvailable($checkIn, $checkOut))
            ->values()
            ->all();
    }

    /**
     * Create a new reservation with double-booking prevention & transactional integrity
     */
    public static function createReservation(array $data): Reservation
    {
        return DB::transaction(function () use ($data) {
            $roomType = RoomType::lockForUpdate()->findOrFail($data['room_type_id']);
            $checkIn = $data['check_in_date'];
            $checkOut = $data['check_out_date'];
            $source = $data['source'] ?? 'walk_in';
            $channel = ($source === 'website') ? 'website' : 'all';

            // 1. Lock check availability to prevent race condition
            $availableCount = self::getAvailableRoomCount($roomType, $checkIn, $checkOut, $channel);
            if ($availableCount < 1) {
                if ($channel === 'website') {
                    throw new InvalidArgumentException("Maaf, kuota kamar tipe {$roomType->name} untuk pemesanan online tidak tersedia atau sudah habis.");
                }
                throw new InvalidArgumentException("Maaf, tidak ada kamar yang tersedia untuk tipe {$roomType->name} pada rentang tanggal tersebut.");
            }

            // 2. Find or create customer
            $customer = Customer::firstOrCreate(
                ['phone' => $data['customer_phone']],
                [
                    'name' => $data['customer_name'],
                    'identity_type' => $data['customer_identity_type'] ?? 'KTP',
                    'identity_number' => $data['customer_identity_number'] ?? null,
                    'company' => $data['customer_company'] ?? null,
                    'address' => $data['customer_address'] ?? null,
                ]
            );

            // Update customer details if provided
            $customer->update(array_filter([
                'name' => $data['customer_name'],
                'identity_type' => $data['customer_identity_type'] ?? null,
                'identity_number' => $data['customer_identity_number'] ?? null,
                'company' => $data['customer_company'] ?? null,
                'address' => $data['customer_address'] ?? null,
            ]));

            // 3. Calculate financial breakdown & addons
            $addonItems = $data['addon_items'] ?? [];
            $addonTotal = 0;
            foreach ($addonItems as $ai) {
                $qty = max(1, (int) ($ai['quantity'] ?? 1));
                $price = max(0, (int) ($ai['unit_price'] ?? 0));
                $addonTotal += ($qty * $price);
            }

            $source = $data['source'] ?? 'walk_in';
            $applyTax = isset($data['apply_tax']) ? (bool) $data['apply_tax'] : true;
            $customRate = isset($data['custom_rate_per_night']) && (int) $data['custom_rate_per_night'] > 0 ? (int) $data['custom_rate_per_night'] : null;

            if ($source === 'ota_manual' && !empty($data['ota_gross_amount']) && (int) $data['ota_gross_amount'] > 0) {
                $gross = (int) $data['ota_gross_amount'];
                $commission = (int) ($data['ota_commission'] ?? 0);
                $net = max(0, $gross - $commission);
                $subtotal = $gross;
                $taxAmount = 0;
                $serviceChargeAmount = 0;
                $discountAmount = 0;
                $totalAmount = $gross + $addonTotal;
                $otaGross = $gross;
                $otaCommission = $commission;
                $otaNetExpected = $net;
            } else {
                $breakdown = PricingService::calculateStay(
                    $roomType,
                    $checkIn,
                    $checkOut,
                    $data['promo_code'] ?? null,
                    $data['manual_discount'] ?? 0,
                    $data['extra_guests'] ?? 0,
                    $customRate,
                    $applyTax,
                    $addonTotal
                );
                $subtotal = $breakdown['subtotal'];
                $discountAmount = $breakdown['discount_amount'];
                $taxAmount = $breakdown['tax_amount'];
                $serviceChargeAmount = $breakdown['service_charge_amount'];
                $totalAmount = $breakdown['total_amount'];
                $otaGross = null;
                $otaCommission = null;
                $otaNetExpected = null;
            }

            // 4. Generate unique reservation number
            $resNumber = NumberingSequence::next('RESERVATION');

            $status = match ($source) {
                'website' => 'pending',
                default => 'confirmed',
            };

            $holdHours = (int) HotelSetting::get('website_hold_hours', 2);
            $expiresAt = ($source === 'website') ? now()->addHours($holdHours) : null;

            // 5. Create reservation record
            $reservation = Reservation::create([
                'reservation_number' => $resNumber,
                'customer_id' => $customer->id,
                'room_type_id' => $roomType->id,
                'source' => $source,
                'ota_name' => $source === 'ota_manual' ? ($data['ota_name'] ?? 'Traveloka') : null,
                'ota_reference_number' => $source === 'ota_manual' ? ($data['ota_reference_number'] ?? null) : null,
                'ota_gross_amount' => $otaGross,
                'ota_commission' => $otaCommission,
                'ota_net_expected' => $otaNetExpected,
                'ota_settlement_status' => $source === 'ota_manual' ? ($data['ota_settlement_status'] ?? 'pending') : null,
                'check_in_date' => $checkIn,
                'check_in_time' => $data['check_in_time'] ?? '14:00',
                'check_out_date' => $checkOut,
                'adults' => $data['adults'] ?? 1,
                'children' => $data['children'] ?? 0,
                'infants' => $data['infants'] ?? 0,
                'status' => $status,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'service_charge_amount' => $serviceChargeAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $data['initial_paid_amount'] ?? 0,
                'payment_status' => ($data['initial_paid_amount'] ?? 0) >= $totalAmount ? 'paid' : (($data['initial_paid_amount'] ?? 0) > 0 ? 'partially_paid' : 'unpaid'),
                'expires_at' => $expiresAt,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Save Addon service items
            foreach ($addonItems as $ai) {
                $name = trim($ai['item_name'] ?? ($ai['name'] ?? ''));
                if ($name === '') continue;
                $qty = max(1, (int) ($ai['quantity'] ?? 1));
                $price = max(0, (int) ($ai['unit_price'] ?? 0));
                ReservationServiceItem::create([
                    'reservation_id' => $reservation->id,
                    'additional_service_id' => $ai['additional_service_id'] ?? null,
                    'item_name' => $name,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $qty * $price,
                ]);
            }

            // 6. Optional: Allocate physical room immediately if selected
            if (!empty($data['room_id'])) {
                $room = Room::findOrFail($data['room_id']);
                if (!$room->isAvailable($checkIn, $checkOut)) {
                    throw new InvalidArgumentException("Kamar fisik nomor {$room->room_number} sedang terisi atau tidak tersedia.");
                }

                ReservationRoom::create([
                    'reservation_id' => $reservation->id,
                    'room_id' => $room->id,
                    'assigned_from' => $checkIn,
                    'assigned_to' => $checkOut,
                    'notes' => 'Alokasi awal reservasi',
                ]);
            }

            AuditLog::record('create_reservation', $reservation, null, $reservation->toArray(), "Reservasi {$resNumber} via {$source}");

            return $reservation;
        });
    }

    /**
     * Update/Edit existing reservation details
     */
    public static function updateReservation(Reservation $reservation, array $data): Reservation
    {
        return DB::transaction(function () use ($reservation, $data) {
            $oldData = $reservation->toArray();

            // 1. Update customer profile
            if ($reservation->customer) {
                $custUpdates = [];
                if (isset($data['customer_name'])) $custUpdates['name'] = $data['customer_name'];
                if (isset($data['customer_phone'])) $custUpdates['phone'] = $data['customer_phone'];
                if (isset($data['customer_identity_type'])) $custUpdates['identity_type'] = $data['customer_identity_type'];
                if (isset($data['customer_identity_number'])) $custUpdates['identity_number'] = $data['customer_identity_number'];
                if (isset($data['customer_company'])) $custUpdates['company'] = $data['customer_company'];
                if (isset($data['customer_address'])) $custUpdates['address'] = $data['customer_address'];
                if (!empty($custUpdates)) {
                    $reservation->customer->update($custUpdates);
                }
            }

            // 2. Dates & Room Type
            $checkIn = isset($data['check_in_date']) ? Carbon::parse($data['check_in_date'])->toDateString() : $reservation->check_in_date->toDateString();
            $checkOut = isset($data['check_out_date']) ? Carbon::parse($data['check_out_date'])->toDateString() : $reservation->check_out_date->toDateString();
            $roomTypeId = $data['room_type_id'] ?? $reservation->room_type_id;
            $roomType = RoomType::findOrFail($roomTypeId);

            $addonTotal = $reservation->serviceItems()->sum('subtotal');
            $source = $data['source'] ?? $reservation->source;
            $applyTax = isset($data['apply_tax']) ? (bool) $data['apply_tax'] : true;
            $customRate = isset($data['custom_rate_per_night']) && (int) $data['custom_rate_per_night'] > 0 ? (int) $data['custom_rate_per_night'] : null;

            if ($source === 'ota_manual' && !empty($data['ota_gross_amount']) && (int) $data['ota_gross_amount'] > 0) {
                $gross = (int) $data['ota_gross_amount'];
                $commission = (int) ($data['ota_commission'] ?? 0);
                $net = max(0, $gross - $commission);
                $subtotal = $gross;
                $taxAmount = 0;
                $serviceChargeAmount = 0;
                $totalAmount = $gross + $addonTotal;
                $otaGross = $gross;
                $otaCommission = $commission;
                $otaNetExpected = $net;
            } else {
                $breakdown = PricingService::calculateStay(
                    $roomType,
                    $checkIn,
                    $checkOut,
                    $data['promo_code'] ?? null,
                    $data['manual_discount'] ?? ($reservation->discount_amount ?? 0),
                    $data['extra_guests'] ?? 0,
                    $customRate,
                    $applyTax,
                    $addonTotal
                );
                $subtotal = $breakdown['subtotal'];
                $taxAmount = $breakdown['tax_amount'];
                $serviceChargeAmount = $breakdown['service_charge_amount'];
                $totalAmount = $breakdown['total_amount'];
                $otaGross = null;
                $otaCommission = null;
                $otaNetExpected = null;
            }

            $updatePayload = [
                'room_type_id' => $roomType->id,
                'source' => $source,
                'check_in_date' => $checkIn,
                'check_in_time' => $data['check_in_time'] ?? '14:00',
                'check_out_date' => $checkOut,
                'adults' => $data['adults'] ?? $reservation->adults,
                'children' => $data['children'] ?? $reservation->children,
                'infants' => $data['infants'] ?? $reservation->infants,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'service_charge_amount' => $serviceChargeAmount,
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? $reservation->notes,
            ];

            if ($source === 'ota_manual') {
                $updatePayload['ota_name'] = $data['ota_name'] ?? $reservation->ota_name;
                $updatePayload['ota_reference_number'] = $data['ota_reference_number'] ?? $reservation->ota_reference_number;
                $updatePayload['ota_gross_amount'] = $otaGross;
                $updatePayload['ota_commission'] = $otaCommission;
                $updatePayload['ota_net_expected'] = $otaNetExpected;
            }

            $updatePayload['payment_status'] = $reservation->paid_amount >= $totalAmount ? 'paid' : ($reservation->paid_amount > 0 ? 'partially_paid' : 'unpaid');

            $reservation->update($updatePayload);

            // Re-allocate physical room if provided
            if (array_key_exists('room_id', $data)) {
                $targetRoomId = $data['room_id'];
                if (!empty($targetRoomId)) {
                    $room = Room::findOrFail($targetRoomId);
                    $isOccupied = ReservationRoom::where('room_id', $room->id)
                        ->where('reservation_id', '!=', $reservation->id)
                        ->whereHas('reservation', fn($q) => $q->whereNotIn('status', ['cancelled', 'expired']))
                        ->where(function ($q) use ($checkIn, $checkOut) {
                            $q->whereDate('assigned_from', '<', $checkOut)
                              ->whereDate('assigned_to', '>', $checkIn);
                        })->exists();

                    if ($isOccupied) {
                        throw new InvalidArgumentException("Kamar fisik nomor {$room->room_number} sedang terisi pada tanggal tersebut.");
                    }

                    ReservationRoom::where('reservation_id', $reservation->id)->delete();
                    ReservationRoom::create([
                        'reservation_id' => $reservation->id,
                        'room_id' => $room->id,
                        'assigned_from' => $checkIn,
                        'assigned_to' => $checkOut,
                        'notes' => 'Alokasi kamar diperbarui staf',
                    ]);
                } else {
                    ReservationRoom::where('reservation_id', $reservation->id)->delete();
                }
            }

            AuditLog::record('update_reservation', $reservation, $oldData, $reservation->toArray(), "Koreksi/Update reservasi #{$reservation->reservation_number}");

            return $reservation;
        });
    }
}
