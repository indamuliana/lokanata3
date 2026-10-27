<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InvoiceController extends Controller
{
    public function downloadPdf($id)
    {
        $invoice = Invoice::with(['reservation', 'items', 'paymentRecords'])->findOrFail($id);
        
        // Generate UUID if not exists
        if (!$invoice->qr_verification_uuid) {
            $invoice->qr_verification_uuid = (string) \Illuminate\Support\Str::uuid();
            $invoice->save();
        }

        $verifyUrl = route('invoice.verify', $invoice->qr_verification_uuid);
        
        // Generate QR code base64 for embedding in PDF
        $qrCode = base64_encode(QrCode::format('svg')->size(100)->generate($verifyUrl));

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'qrCode'));
        
        return $pdf->stream('invoice-'.$invoice->invoice_number.'.pdf');
    }

    public function verify($uuid)
    {
        $invoice = Invoice::with(['reservation'])->where('qr_verification_uuid', $uuid)->firstOrFail();
        
        return view('verify-invoice', compact('invoice'));
    }
}
