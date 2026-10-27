<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\DiningPackage;
use App\Models\DiningReservation;
use App\Models\DiningServiceItem;
use App\Models\HotelSetting;
use App\Models\NumberingSequence;
use App\Models\Payment;
use App\Services\SeasonService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FineDiningService
{
    /**
     * Calculate cost breakdown for fine dining reservation
     */
    public static function calculatePricing(
        ?DiningPackage $package,
        int $pax,
        int $customPricePerPax = 0,
        int $additionalCharges = 0,
        int $discount = 0,
        bool $applyTax = true,
        ?string $reservationDate = null
    ): array {
        $isHighSeason = $reservationDate ? SeasonService::isHighSeason($reservationDate) : false;
        $seasonInfo = $reservationDate ? SeasonService::getSeasonInfo($reservationDate) : ['is_high_season' => false, 'label' => 'Normal'];

        if ($customPricePerPax > 0) {
            $pricePerPax = $customPricePerPax;
        } elseif ($package) {
            $pricePerPax = ($isHighSeason && !empty($package->high_price_per_pax) && $package->high_price_per_pax > 0)
                ? $package->high_price_per_pax
                : $package->price_per_pax;
        } else {
            $pricePerPax = 65000;
        }

        $subtotal = $pax * $pricePerPax;

        $taxPercent = $applyTax ? (int) HotelSetting::get('tax_percentage', 10) : 0;
        $taxableAmount = max(0, $subtotal - $discount);
        $taxAmount = (int) round($taxableAmount * $taxPercent / 100);
        $totalAmount = $taxableAmount + $taxAmount + $additionalCharges;

        return [
            'price_per_pax' => $pricePerPax,
            'subtotal' => $subtotal,
            'additional_charges' => $additionalCharges,
            'discount_amount' => $discount,
            'apply_tax' => $applyTax,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'is_high_season' => $seasonInfo['is_high_season'] ?? false,
            'season_label' => $seasonInfo['label'] ?? 'Normal',
        ];
    }

    /**
     * Create fine dining reservation
     */
    public static function createReservation(array $data): DiningReservation
    {
        return DB::transaction(function () use ($data) {
            $pax = max(1, (int) ($data['pax'] ?? 1));
            $package = isset($data['dining_package_id']) ? DiningPackage::find($data['dining_package_id']) : null;

            if ($package && $pax < $package->min_pax) {
                throw new InvalidArgumentException("Minimal pemesanan untuk {$package->name} adalah {$package->min_pax} porsi/pax.");
            }

            // Customer resolution
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
                ]
            );

            // Calculate extra services charges
            $additionalCharges = 0;
            $serviceItems = $data['service_items'] ?? [];
            if (!empty($serviceItems)) {
                foreach ($serviceItems as $item) {
                    $qty = max(1, (int) ($item['quantity'] ?? 1));
                    $unitPrice = max(0, (int) ($item['unit_price'] ?? 0));
                    $additionalCharges += ($qty * $unitPrice);
                }
            } else {
                $additionalCharges = (int) ($data['additional_charges'] ?? 0);
            }

            $applyTax = isset($data['apply_tax']) ? (bool) $data['apply_tax'] : true;

            $pricing = self::calculatePricing(
                $package,
                $pax,
                $data['custom_price_per_pax'] ?? 0,
                $additionalCharges,
                $data['discount_amount'] ?? 0,
                $applyTax,
                $data['reservation_date'] ?? null
            );

            $resNumber = NumberingSequence::next('DNR');

            $reservation = DiningReservation::create([
                'reservation_number' => $resNumber,
                'customer_id' => $customer->id,
                'group_name' => !empty($data['group_name']) ? trim($data['group_name']) : null,
                'pic_name' => !empty($data['pic_name']) ? trim($data['pic_name']) : null,
                'pic_phone' => !empty($data['pic_phone']) ? trim($data['pic_phone']) : null,
                'activity_link' => !empty($data['activity_link']) ? trim($data['activity_link']) : null,
                'is_wikrama_teachers' => (bool) ($data['is_wikrama_teachers'] ?? false),
                'dining_package_id' => $package?->id,
                'custom_menu_name' => !empty($data['custom_menu_name']) ? trim($data['custom_menu_name']) : null,
                'table_number_or_area' => $data['table_number_or_area'] ?? 'Area Utama Restoran',
                'reservation_date' => $data['reservation_date'],
                'time_slot' => $data['time_slot'] ?? '12:00',
                'pax' => $pax,
                'price_per_pax' => $pricing['price_per_pax'],
                'subtotal' => $pricing['subtotal'],
                'additional_charges' => $pricing['additional_charges'],
                'discount_amount' => $pricing['discount_amount'],
                'apply_tax' => $applyTax,
                'tax_amount' => $pricing['tax_amount'],
                'total_amount' => $pricing['total_amount'],
                'paid_amount' => 0,
                'payment_status' => 'unpaid',
                'special_requests' => $data['special_requests'] ?? null,
                'status' => 'confirmed',
                'notes' => $data['notes'] ?? null,
            ]);

            // Save service items
            if (!empty($serviceItems)) {
                foreach ($serviceItems as $item) {
                    $name = trim($item['name'] ?? '');
                    if ($name !== '') {
                        $qty = max(1, (int) ($item['quantity'] ?? 1));
                        $unitPrice = max(0, (int) ($item['unit_price'] ?? 0));
                        $reservation->items()->create([
                            'dining_service_id' => $item['dining_service_id'] ?? null,
                            'name' => $name,
                            'quantity' => $qty,
                            'unit_price' => $unitPrice,
                            'subtotal' => $qty * $unitPrice,
                        ]);
                    }
                }
            }

            if (!empty($data['initial_paid_amount']) && $data['initial_paid_amount'] > 0) {
                self::recordPayment(
                    $reservation,
                    (int) $data['initial_paid_amount'],
                    $data['payment_method'] ?? 'cash',
                    $data['bank_account'] ?? null,
                    $data['reference_number'] ?? null,
                    'Pembayaran DP Fine Dining Syariah'
                );
            }

            AuditLog::record(
                'create_dining_reservation',
                $reservation,
                null,
                $reservation->toArray(),
                "Reservasi fine dining #{$resNumber} ({$pax} pax) berhasil dibuat."
            );

            return $reservation;
        });
    }

    /**
     * Update existing fine dining reservation
     */
    public static function updateReservation(DiningReservation $res, array $data): DiningReservation
    {
        return DB::transaction(function () use ($res, $data) {
            $oldData = $res->toArray();
            $pax = max(1, (int) ($data['pax'] ?? $res->pax));
            $packageId = array_key_exists('dining_package_id', $data) ? $data['dining_package_id'] : $res->dining_package_id;
            $package = $packageId ? DiningPackage::find($packageId) : null;

            if ($package && $pax < $package->min_pax) {
                throw new InvalidArgumentException("Minimal pemesanan untuk {$package->name} adalah {$package->min_pax} porsi/pax.");
            }

            // Sync service items if provided
            $additionalCharges = $res->additional_charges;
            if (isset($data['service_items'])) {
                $res->items()->delete();
                $additionalCharges = 0;
                foreach ($data['service_items'] as $item) {
                    $name = trim($item['name'] ?? '');
                    if ($name !== '') {
                        $qty = max(1, (int) ($item['quantity'] ?? 1));
                        $unitPrice = max(0, (int) ($item['unit_price'] ?? 0));
                        $itemSubtotal = $qty * $unitPrice;
                        $additionalCharges += $itemSubtotal;

                        $res->items()->create([
                            'dining_service_id' => $item['dining_service_id'] ?? null,
                            'name' => $name,
                            'quantity' => $qty,
                            'unit_price' => $unitPrice,
                            'subtotal' => $itemSubtotal,
                        ]);
                    }
                }
            } elseif (isset($data['additional_charges'])) {
                $additionalCharges = (int) $data['additional_charges'];
            }

            $applyTax = isset($data['apply_tax']) ? (bool) $data['apply_tax'] : $res->apply_tax;
            $customPrice = isset($data['custom_price_per_pax']) ? (int) $data['custom_price_per_pax'] : ($package ? 0 : $res->price_per_pax);
            $discount = isset($data['discount_amount']) ? (int) $data['discount_amount'] : $res->discount_amount;

            $pricing = self::calculatePricing(
                $package,
                $pax,
                $customPrice,
                $additionalCharges,
                $discount,
                $applyTax,
                $data['reservation_date'] ?? $res->reservation_date?->toDateString()
            );

            $updatePayload = [
                'pax' => $pax,
                'dining_package_id' => $package?->id,
                'price_per_pax' => $pricing['price_per_pax'],
                'subtotal' => $pricing['subtotal'],
                'additional_charges' => $pricing['additional_charges'],
                'discount_amount' => $pricing['discount_amount'],
                'apply_tax' => $applyTax,
                'tax_amount' => $pricing['tax_amount'],
                'total_amount' => $pricing['total_amount'],
            ];

            if (isset($data['group_name'])) $updatePayload['group_name'] = !empty($data['group_name']) ? trim($data['group_name']) : null;
            if (isset($data['pic_name'])) $updatePayload['pic_name'] = !empty($data['pic_name']) ? trim($data['pic_name']) : null;
            if (isset($data['pic_phone'])) $updatePayload['pic_phone'] = !empty($data['pic_phone']) ? trim($data['pic_phone']) : null;
            if (isset($data['activity_link'])) $updatePayload['activity_link'] = !empty($data['activity_link']) ? trim($data['activity_link']) : null;
            if (isset($data['is_wikrama_teachers'])) $updatePayload['is_wikrama_teachers'] = (bool) $data['is_wikrama_teachers'];
            if (isset($data['custom_menu_name'])) $updatePayload['custom_menu_name'] = !empty($data['custom_menu_name']) ? trim($data['custom_menu_name']) : null;
            if (isset($data['table_number_or_area'])) $updatePayload['table_number_or_area'] = $data['table_number_or_area'];
            if (isset($data['reservation_date'])) $updatePayload['reservation_date'] = $data['reservation_date'];
            if (isset($data['time_slot'])) $updatePayload['time_slot'] = $data['time_slot'];
            if (isset($data['special_requests'])) $updatePayload['special_requests'] = $data['special_requests'];
            if (isset($data['notes'])) $updatePayload['notes'] = $data['notes'];

            // Update customer if provided
            if (!empty($data['customer_name']) || !empty($data['customer_phone'])) {
                $customer = $res->customer;
                if ($customer) {
                    $cPhone = preg_replace('/[^0-9]/', '', $data['customer_phone'] ?? $customer->phone);
                    if (str_starts_with($cPhone, '0')) {
                        $cPhone = '62' . substr($cPhone, 1);
                    }
                    $customer->update([
                        'name' => $data['customer_name'] ?? $customer->name,
                        'phone' => $cPhone,
                    ]);
                }
            }

            // Recalculate payment status
            if ($res->paid_amount >= $pricing['total_amount']) {
                $updatePayload['payment_status'] = 'paid';
            } elseif ($res->paid_amount > 0) {
                $updatePayload['payment_status'] = 'partially_paid';
            } else {
                $updatePayload['payment_status'] = 'unpaid';
            }

            $res->update($updatePayload);

            AuditLog::record(
                'update_dining_reservation',
                $res,
                $oldData,
                $res->toArray(),
                "Reservasi fine dining #{$res->reservation_number} berhasil diperbarui."
            );

            return $res;
        });
    }

    /**
     * Record payment for fine dining reservation
     */
    public static function recordPayment(
        DiningReservation $res,
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
                'dining_reservation_id' => $res->id,
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
                'dining_payment_received',
                $payment,
                null,
                $payment->toArray(),
                "Penerimaan pembayaran resto #{$res->reservation_number} sebesar Rp {$amount}"
            );

            if (\App\Models\ChartOfAccount::count() > 0) {
                AccountingService::postPaymentJournal($payment);
            }

            return $payment;
        });
    }
}
