<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\CashierShift;
use App\Models\DiningReservation;
use App\Models\MeetingReservation;
use App\Models\NightAudit;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class NightAuditService
{
    /**
     * Get pre-audit checklist and status
     */
    public static function getAuditChecklist(string $auditDate): array
    {
        $openShifts = CashierShift::where('status', 'open')->count();

        // In-house reservations on audit date
        $inHouseReservations = Reservation::where('status', 'checked_in')
            ->whereDate('check_in_date', '<=', $auditDate)
            ->whereDate('check_out_date', '>=', $auditDate)
            ->with(['roomType', 'customer'])
            ->get();

        // Meeting and dining on audit date
        $meetingsToday = MeetingReservation::whereDate('event_date', $auditDate)
            ->where('status', '!=', 'cancelled')
            ->get();

        $diningsToday = DiningReservation::whereDate('reservation_date', $auditDate)
            ->where('status', '!=', 'cancelled')
            ->get();

        $alreadyAudited = NightAudit::whereDate('audit_date', $auditDate)->exists();

        return [
            'audit_date' => $auditDate,
            'already_audited' => $alreadyAudited,
            'open_shifts_count' => $openShifts,
            'occupied_rooms_count' => $inHouseReservations->count(),
            'in_house_reservations' => $inHouseReservations,
            'meetings_count' => $meetingsToday->count(),
            'dinings_count' => $diningsToday->count(),
            'estimated_room_revenue' => $inHouseReservations->sum(function ($r) {
                return (int) round($r->subtotal / max(1, $r->nights_count));
            }),
            'estimated_meeting_revenue' => $meetingsToday->sum('subtotal'),
            'estimated_dining_revenue' => $diningsToday->sum('subtotal'),
        ];
    }

    /**
     * Perform Night Audit and post revenue recognition journals
     */
    public static function performNightAudit(User $auditor, string $auditDate, ?string $notes = null): NightAudit
    {
        return DB::transaction(function () use ($auditor, $auditDate, $notes) {
            $checklist = self::getAuditChecklist($auditDate);

            if ($checklist['already_audited']) {
                throw new InvalidArgumentException("Audit malam untuk tanggal {$auditDate} sudah pernah dilakukan sebelumnya.");
            }

            $totalRoomRevenue = 0;

            $taxPercent = (int) \App\Models\HotelSetting::get('tax_percentage', 10);

            // Recognize daily room revenue for each in-house reservation
            foreach ($checklist['in_house_reservations'] as $res) {
                $dailyRoomRate = (int) round($res->subtotal / max(1, $res->nights_count));
                $dailyTax = (int) round(($dailyRoomRate * $taxPercent) / 100);

                AccountingService::postStayRevenueRecognition(
                    $res,
                    $dailyRoomRate,
                    $dailyTax,
                    $auditDate
                );

                $totalRoomRevenue += $dailyRoomRate;
            }

            $nightAudit = NightAudit::create([
                'audit_date' => $auditDate,
                'performed_by' => $auditor->id,
                'performed_at' => now(),
                'rooms_occupied_count' => $checklist['occupied_rooms_count'],
                'total_room_revenue' => $totalRoomRevenue,
                'total_meeting_revenue' => $checklist['estimated_meeting_revenue'],
                'total_dining_revenue' => $checklist['estimated_dining_revenue'],
                'notes' => $notes,
            ]);

            AuditLog::record(
                'perform_night_audit',
                $nightAudit,
                null,
                $nightAudit->toArray(),
                "Penutupan hari (Night Audit) tanggal {$auditDate} berhasil diselesaikan oleh {$auditor->name}. Kamar terisi: {$checklist['occupied_rooms_count']}."
            );

            return $nightAudit;
        });
    }
}
