<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\HotelSetting;
use App\Models\MeetingPackage;
use App\Models\MeetingReservation;
use App\Models\MeetingReservationItem;
use App\Models\MeetingRoom;
use App\Models\MeetingService;
use App\Models\NumberingSequence;
use App\Models\Payment;
use App\Services\SeasonService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class MeetingRoomService
{
    /**
     * Check if a meeting room is free at the given date and time range
     */
    public static function checkAvailability(
        MeetingRoom $room,
        string $date,
        string $startTime,
        string $endTime,
        ?int $ignoreReservationId = null
    ): bool {
        $query = MeetingReservation::where('meeting_room_id', $room->id)
            ->whereDate('event_date', $date)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startTime, $endTime) {
                // Interval overlap: existing_start < new_end AND existing_end > new_start
                $q->where('start_time', '<', $endTime)
                  ->where('end_time', '>', $startTime);
            });

        if ($ignoreReservationId) {
            $query->where('id', '!=', $ignoreReservationId);
        }

        return !$query->exists();
    }

    /**
     * Calculate cost breakdown for meeting room reservation
     */
    public static function calculatePricing(
        MeetingRoom $room,
        string $pricingType,
        int $pax,
        string $startTime,
        string $endTime,
        ?MeetingPackage $package = null,
        int $customBasePrice = 0,
        int $additionalCharges = 0,
        int $discount = 0,
        ?string $eventDate = null
    ): array {
        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);
        $durationHours = max(1, (int) ceil($end->diffInMinutes($start) / 60));

        $isHighSeason = $eventDate ? SeasonService::isHighSeason($eventDate) : false;
        $seasonInfo = $eventDate ? SeasonService::getSeasonInfo($eventDate) : ['is_high_season' => false, 'label' => 'Normal'];

        if ($customBasePrice > 0) {
            $subtotal = $customBasePrice;
        } elseif ($pricingType === 'per_pax') {
            $ratePerPax = $package
                ? (($isHighSeason && !empty($package->high_price_per_pax) && $package->high_price_per_pax > 0) ? $package->high_price_per_pax : $package->price_per_pax)
                : 75000;
            $subtotal = $pax * $ratePerPax;
        } elseif ($pricingType === 'hourly') {
            $hourlyRate = ($isHighSeason && !empty($room->high_hourly_rate) && $room->high_hourly_rate > 0)
                ? $room->high_hourly_rate
                : $room->hourly_rate;
            $subtotal = $durationHours * $hourlyRate;
        } else {
            // all_in flat
            $halfdayRate = ($isHighSeason && !empty($room->high_halfday_rate) && $room->high_halfday_rate > 0)
                ? $room->high_halfday_rate
                : $room->halfday_rate;
            $fulldayRate = ($isHighSeason && !empty($room->high_fullday_rate) && $room->high_fullday_rate > 0)
                ? $room->high_fullday_rate
                : $room->fullday_rate;
            $subtotal = ($durationHours <= 4) ? $halfdayRate : $fulldayRate;
        }

        $taxPercent = (int) HotelSetting::get('tax_percentage', 10);
        $taxableAmount = max(0, $subtotal - $discount);
        $taxAmount = (int) round($taxableAmount * $taxPercent / 100);
        $totalAmount = $taxableAmount + $taxAmount + $additionalCharges;

        return [
            'duration_hours' => $durationHours,
            'subtotal' => $subtotal,
            'additional_charges' => $additionalCharges,
            'discount_amount' => $discount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'is_high_season' => $seasonInfo['is_high_season'] ?? false,
            'season_label' => $seasonInfo['label'] ?? 'Normal',
        ];
    }

    /**
     * Create meeting reservation with strict conflict prevention
     */
    public static function createReservation(array $data): MeetingReservation
    {
        return DB::transaction(function () use ($data) {
            $room = MeetingRoom::findOrFail($data['meeting_room_id']);

            // 1. Capacity check
            $pax = (int) ($data['pax'] ?? 1);
            if ($pax > $room->capacity) {
                throw new InvalidArgumentException("Jumlah peserta ({$pax} orang) melebihi kapasitas maksimum {$room->name} ({$room->capacity} orang).");
            }

            // 2. Conflict check
            $isFree = self::checkAvailability(
                $room,
                $data['event_date'],
                $data['start_time'],
                $data['end_time']
            );

            if (!$isFree) {
                throw new InvalidArgumentException("Jadwal bentrok: Ruangan {$room->name} sudah dipesan oleh acara lain pada tanggal {$data['event_date']} jam {$data['start_time']} - {$data['end_time']}.");
            }

            // 3. Customer resolution
            $customerPhone = preg_replace('/[^0-9]/', '', $data['customer_phone']);
            if (str_starts_with($customerPhone, '0')) {
                $customerPhone = '62' . substr($customerPhone, 1);
            }

            $customer = Customer::firstOrCreate(
                ['phone' => $customerPhone],
                [
                    'name' => $data['customer_name'],
                    'identity_type' => $data['customer_identity_type'] ?? 'KTP',
                    'identity_number' => $data['customer_identity_number'] ?? null,
                    'company' => $data['customer_company'] ?? null,
                ]
            );

            // 4. Calculate items total if provided
            $items = $data['items'] ?? [];
            $itemsCharge = 0;
            foreach ($items as $it) {
                $qty = max(1, (int) ($it['quantity'] ?? 1));
                $price = max(0, (int) ($it['unit_price'] ?? 0));
                $itemsCharge += ($qty * $price);
            }

            $additionalCharges = isset($data['additional_charges']) ? (int) $data['additional_charges'] : $itemsCharge;
            if ($itemsCharge > 0 && $additionalCharges < $itemsCharge) {
                $additionalCharges = $itemsCharge;
            }

            // 5. Pricing calculation
            $pricing = self::calculatePricing(
                $room,
                $data['pricing_type'] ?? 'all_in',
                $pax,
                $data['start_time'],
                $data['end_time'],
                isset($data['meeting_package_id']) ? MeetingPackage::find($data['meeting_package_id']) : null,
                $data['custom_base_price'] ?? 0,
                $additionalCharges,
                $data['discount_amount'] ?? 0,
                $data['event_date'] ?? null
            );

            $resNumber = NumberingSequence::next('MTR');

            $reservation = MeetingReservation::create([
                'reservation_number' => $resNumber,
                'customer_id' => $customer->id,
                'meeting_room_id' => $room->id,
                'meeting_package_id' => $data['meeting_package_id'] ?? null,
                'event_name' => $data['event_name'],
                'event_date' => $data['event_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'pax' => $pax,
                'pricing_type' => $data['pricing_type'] ?? 'all_in',
                'subtotal' => $pricing['subtotal'],
                'additional_charges' => $pricing['additional_charges'],
                'discount_amount' => $pricing['discount_amount'],
                'tax_amount' => $pricing['tax_amount'],
                'total_amount' => $pricing['total_amount'],
                'paid_amount' => 0,
                'payment_status' => 'unpaid',
                'status' => 'confirmed',
                'notes' => $data['notes'] ?? null,
                'facility_notes' => $data['facility_notes'] ?? null,
            ]);

            // Save items if any
            foreach ($items as $it) {
                $name = trim($it['name'] ?? '');
                if ($name === '') continue;
                $qty = max(1, (int) ($it['quantity'] ?? 1));
                $price = max(0, (int) ($it['unit_price'] ?? 0));
                MeetingReservationItem::create([
                    'meeting_reservation_id' => $reservation->id,
                    'meeting_service_id' => $it['meeting_service_id'] ?? null,
                    'name' => $name,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $qty * $price,
                ]);
            }

            // Initial payment if any
            if (!empty($data['initial_paid_amount']) && $data['initial_paid_amount'] > 0) {
                self::recordPayment(
                    $reservation,
                    (int) $data['initial_paid_amount'],
                    $data['payment_method'] ?? 'cash',
                    $data['bank_account'] ?? null,
                    $data['reference_number'] ?? null,
                    'Pembayaran DP / Uang Muka Reservasi Ruang Meeting'
                );
            }

            AuditLog::record(
                'create_meeting_reservation',
                $reservation,
                null,
                $reservation->toArray(),
                "Reservasi meeting #{$resNumber} ({$room->name}) berhasil dibuat."
            );

            return $reservation;
        });
    }

    /**
     * Record payment for meeting reservation
     */
    public static function recordPayment(
        MeetingReservation $res,
        int $amount,
        string $method = 'cash',
        ?string $bank = null,
        ?string $reference = null,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use ($res, $amount, $method, $bank, $reference, $notes) {
            if ($amount <= 0) {
                throw new InvalidArgumentException("Nominal pembayaran harus lebih besar dari Rp 0.");
            }

            $paymentNumber = NumberingSequence::next('RECEIPT');

            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'meeting_reservation_id' => $res->id,
                'amount' => $amount,
                'payment_method' => $method,
                'bank_account' => $bank,
                'reference_number' => $reference,
                'is_refund' => false,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'notes' => $notes,
            ]);

            $newPaid = $res->paid_amount + $amount;
            $newStatus = ($newPaid >= $res->total_amount) ? 'paid' : 'partially_paid';

            $res->update([
                'paid_amount' => $newPaid,
                'payment_status' => $newStatus,
            ]);

            AuditLog::record(
                'meeting_payment_received',
                $payment,
                null,
                $payment->toArray(),
                "Penerimaan pembayaran meeting #{$res->reservation_number} sebesar Rp {$amount}"
            );

            if (\App\Models\ChartOfAccount::count() > 0) {
                AccountingService::postPaymentJournal($payment);
            }

            return $payment;
        });
    }

    /**
     * Add additional service item to existing meeting reservation
     */
    public static function addServiceItem(MeetingReservation $res, array $itemData): MeetingReservationItem
    {
        return DB::transaction(function () use ($res, $itemData) {
            $name = trim($itemData['name'] ?? '');
            if ($name === '') {
                throw new InvalidArgumentException("Nama layanan tambahan tidak boleh kosong.");
            }

            $qty = max(1, (int) ($itemData['quantity'] ?? 1));
            $price = max(0, (int) ($itemData['unit_price'] ?? 0));
            $subtotal = $qty * $price;

            $item = MeetingReservationItem::create([
                'meeting_reservation_id' => $res->id,
                'meeting_service_id' => $itemData['meeting_service_id'] ?? null,
                'name' => $name,
                'quantity' => $qty,
                'unit_price' => $price,
                'subtotal' => $subtotal,
            ]);

            // Recalculate additional charges and total
            $totalItemsCharge = $res->items()->sum('subtotal');
            $taxPercent = (int) HotelSetting::get('tax_percentage', 10);
            $taxableAmount = max(0, $res->subtotal - $res->discount_amount);
            $taxAmount = (int) round($taxableAmount * $taxPercent / 100);
            $newTotalAmount = $taxableAmount + $taxAmount + $totalItemsCharge;

            $newStatus = ($res->paid_amount >= $newTotalAmount) ? 'paid' : ($res->paid_amount > 0 ? 'partially_paid' : 'unpaid');

            $res->update([
                'additional_charges' => $totalItemsCharge,
                'tax_amount' => $taxAmount,
                'total_amount' => $newTotalAmount,
                'payment_status' => $newStatus,
            ]);

            AuditLog::record(
                'add_meeting_service_item',
                $item,
                null,
                $item->toArray(),
                "Penambahan layanan tambahan '{$name}' (x{$qty}) pada reservasi #{$res->reservation_number}"
            );

            return $item;
        });
    }
}
