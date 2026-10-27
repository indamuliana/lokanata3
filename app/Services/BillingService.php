<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\HotelSetting;
use App\Models\Invoice;
use App\Models\NumberingSequence;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\ReservationServiceItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BillingService
{
    /**
     * Record a payment (DP, installment, or settlement)
     */
    public static function recordPayment(
        Reservation $reservation,
        int $amount,
        string $method = 'cash',
        ?string $bank = null,
        ?string $reference = null,
        ?string $proofPath = null,
        ?string $notes = null
    ): Payment {
        return DB::transaction(function () use ($reservation, $amount, $method, $bank, $reference, $proofPath, $notes) {
            if ($amount <= 0) {
                throw new InvalidArgumentException("Nominal pembayaran harus lebih besar dari Rp 0.");
            }

            // Find or generate active invoice
            $invoice = $reservation->invoices()->latest()->first();
            if (!$invoice) {
                $invoice = self::generateInvoice($reservation);
            }

            $paymentNumber = NumberingSequence::next('RECEIPT');

            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'invoice_id' => $invoice->id,
                'reservation_id' => $reservation->id,
                'amount' => $amount,
                'payment_method' => $method,
                'bank_account' => $bank,
                'reference_number' => $reference,
                'proof_file_path' => $proofPath,
                'is_refund' => false,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'notes' => $notes,
            ]);

            // Update reservation paid amount and status
            $newPaidTotal = $reservation->paid_amount + $amount;
            $newPaymentStatus = ($newPaidTotal >= $reservation->total_amount) ? 'paid' : 'partially_paid';

            $reservation->update([
                'paid_amount' => $newPaidTotal,
                'payment_status' => $newPaymentStatus,
            ]);

            // Update invoice
            $invoice->update([
                'paid_amount' => $invoice->paid_amount + $amount,
                'status' => ($invoice->paid_amount + $amount >= $invoice->total_amount) ? 'paid' : 'partially_paid',
            ]);

            AuditLog::record('payment_received', $payment, null, $payment->toArray(), "Penerimaan pembayaran {$paymentNumber} sebesar Rp {$amount} ({$method})");

            // Post double-entry journal if accounting accounts exist
            if (\App\Models\ChartOfAccount::count() > 0) {
                AccountingService::postPaymentJournal($payment);
            }

            return $payment;
        });
    }

    /**
     * Process refund with audit log
     */
    public static function recordRefund(Reservation $reservation, int $amount, string $reason): Payment
    {
        return DB::transaction(function () use ($reservation, $amount, $reason) {
            if ($amount <= 0 || $amount > $reservation->paid_amount) {
                throw new InvalidArgumentException("Nominal refund tidak valid atau melebihi total yang telah dibayar.");
            }

            $paymentNumber = NumberingSequence::next('RECEIPT');

            $refundPayment = Payment::create([
                'payment_number' => $paymentNumber,
                'reservation_id' => $reservation->id,
                'amount' => $amount,
                'payment_method' => 'cash',
                'is_refund' => true,
                'refund_reason' => $reason,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
                'notes' => "Refund: {$reason}",
            ]);

            $newPaid = max(0, $reservation->paid_amount - $amount);
            $reservation->update([
                'paid_amount' => $newPaid,
                'payment_status' => $newPaid === 0 ? 'unpaid' : 'partially_paid',
            ]);

            AuditLog::record('payment_refund', $refundPayment, null, $refundPayment->toArray(), "Refund {$paymentNumber} sebesar Rp {$amount}. Alasan: {$reason}");

            if (\App\Models\ChartOfAccount::count() > 0) {
                AccountingService::postPaymentJournal($refundPayment);
            }

            return $refundPayment;
        });
    }

    /**
     * Add extra service item to reservation (Extra Bed, Breakfast, Laundry, dll.)
     */
    public static function addServiceItem(
        Reservation $reservation,
        string $itemName,
        int $quantity,
        int $unitPrice,
        ?int $serviceId = null
    ): ReservationServiceItem {
        return DB::transaction(function () use ($reservation, $itemName, $quantity, $unitPrice, $serviceId) {
            $subtotal = $quantity * $unitPrice;

            $item = ReservationServiceItem::create([
                'reservation_id' => $reservation->id,
                'additional_service_id' => $serviceId,
                'item_name' => $itemName,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ]);

            // Recalculate tax & total on reservation
            $newSubtotal = $reservation->subtotal + $subtotal;
            $taxPercent = (int) HotelSetting::get('tax_percentage', 10);
            $newTax = (int) round(($newSubtotal - $reservation->discount_amount) * $taxPercent / 100);
            $newTotal = ($newSubtotal - $reservation->discount_amount) + $newTax + $reservation->service_charge_amount;

            $reservation->update([
                'subtotal' => $newSubtotal,
                'tax_amount' => $newTax,
                'total_amount' => $newTotal,
                'payment_status' => ($reservation->paid_amount >= $newTotal) ? 'paid' : ($reservation->paid_amount > 0 ? 'partially_paid' : 'unpaid'),
            ]);

            // Update active invoice if exists
            $invoice = $reservation->invoices()->latest()->first();
            if ($invoice) {
                $invoice->update([
                    'subtotal' => $newSubtotal,
                    'tax_amount' => $newTax,
                    'total_amount' => $newTotal,
                ]);
            }

            AuditLog::record('add_service_item', $item, null, $item->toArray(), "Tambah layanan {$itemName} x {$quantity} pada reservasi #{$reservation->reservation_number}");

            return $item;
        });
    }

    /**
     * Generate or get active invoice
     */
    public static function generateInvoice(Reservation $reservation): Invoice
    {
        $existing = $reservation->invoices()->latest()->first();
        if ($existing) {
            return $existing;
        }

        $invNumber = NumberingSequence::next('INVOICE');
        $invoice = Invoice::create([
            'invoice_number' => $invNumber,
            'reservation_id' => $reservation->id,
            'subtotal' => $reservation->subtotal,
            'discount_amount' => $reservation->discount_amount,
            'tax_amount' => $reservation->tax_amount,
            'service_charge_amount' => $reservation->service_charge_amount,
            'total_amount' => $reservation->total_amount,
            'paid_amount' => $reservation->paid_amount,
            'status' => ($reservation->paid_amount >= $reservation->total_amount) ? 'paid' : ($reservation->paid_amount > 0 ? 'partially_paid' : 'unpaid'),
        ]);

        $invoice->getOrCreateQrToken();

        return $invoice;
    }
}
