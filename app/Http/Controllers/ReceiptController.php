<?php

namespace App\Http\Controllers;

use App\Models\DiningReservation;
use App\Models\Invoice;
use App\Models\MeetingReservation;
use App\Models\Payment;
use App\Models\PublicVerificationToken;
use App\Models\Reservation;
use App\Services\BillingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReceiptController extends Controller
{
    /**
     * Download or stream official A4 Invoice PDF
     */
    public function downloadPdf($reservationId)
    {
        $reservation = Reservation::with(['customer', 'roomType', 'allocatedRooms.room', 'serviceItems', 'payments'])->findOrFail($reservationId);
        $invoice = BillingService::generateInvoice($reservation);
        $qrToken = $invoice->getOrCreateQrToken();

        $verifyUrl = route('document.verify', ['token' => $qrToken]);
        $qrCodeBase64 = base64_encode(QrCode::format('svg')->size(110)->generate($verifyUrl));

        $pdf = Pdf::loadView('pdf.invoice', compact('reservation', 'invoice', 'qrCodeBase64'));
        return $pdf->stream("Invoice-{$invoice->invoice_number}.pdf");
    }

    /**
     * Render Thermal 80mm Receipt view (ready to print via POS printer)
     */
    public function printThermal($reservationId)
    {
        $reservation = Reservation::with(['customer', 'roomType', 'allocatedRooms.room', 'serviceItems', 'payments'])->findOrFail($reservationId);
        $invoice = BillingService::generateInvoice($reservation);
        $qrToken = $invoice->getOrCreateQrToken();

        $verifyUrl = route('document.verify', ['token' => $qrToken]);
        $qrCodeSvg = QrCode::format('svg')->size(90)->generate($verifyUrl);

        return view('print.thermal-receipt', compact('reservation', 'invoice', 'qrCodeSvg'));
    }

    /**
     * Download or stream A4 PDF for Meeting Reservation
     */
    public function downloadMeetingPdf($id)
    {
        $reservation = MeetingReservation::with(['customer', 'meetingRoom', 'meetingPackage', 'payments', 'items'])->findOrFail($id);
        $qrToken = $reservation->getOrCreateQrToken();

        $verifyUrl = route('document.verify', ['token' => $qrToken]);
        $qrCodeBase64 = base64_encode(QrCode::format('svg')->size(110)->generate($verifyUrl));

        $pdf = Pdf::loadView('pdf.meeting-receipt', compact('reservation', 'qrCodeBase64'));
        return $pdf->stream("Meeting-{$reservation->reservation_number}.pdf");
    }

    /**
     * Render Thermal 80mm Receipt for Meeting Reservation
     */
    public function printMeetingThermal($id)
    {
        $reservation = MeetingReservation::with(['customer', 'meetingRoom', 'meetingPackage', 'payments', 'items'])->findOrFail($id);
        $qrToken = $reservation->getOrCreateQrToken();

        $verifyUrl = route('document.verify', ['token' => $qrToken]);
        $qrCodeSvg = QrCode::format('svg')->size(90)->generate($verifyUrl);

        return view('print.meeting-thermal', compact('reservation', 'qrCodeSvg'));
    }

    /**
     * Download or stream A4 PDF for Dining Reservation
     */
    public function downloadDiningPdf($id)
    {
        $reservation = DiningReservation::with(['customer', 'diningPackage', 'payments'])->findOrFail($id);
        $qrToken = $reservation->getOrCreateQrToken();

        $verifyUrl = route('document.verify', ['token' => $qrToken]);
        $qrCodeBase64 = base64_encode(QrCode::format('svg')->size(110)->generate($verifyUrl));

        $pdf = Pdf::loadView('pdf.dining-receipt', compact('reservation', 'qrCodeBase64'));
        return $pdf->stream("Dining-{$reservation->reservation_number}.pdf");
    }

    /**
     * Render Thermal 80mm Receipt for Dining Reservation
     */
    public function printDiningThermal($id)
    {
        $reservation = DiningReservation::with(['customer', 'diningPackage', 'payments'])->findOrFail($id);
        $qrToken = $reservation->getOrCreateQrToken();

        $verifyUrl = route('document.verify', ['token' => $qrToken]);
        $qrCodeSvg = QrCode::format('svg')->size(90)->generate($verifyUrl);

        return view('print.dining-thermal', compact('reservation', 'qrCodeSvg'));
    }

    /**
     * Public QR Code Verification Page (No PII leak per PRD 17.2)
     */
    public function verifyPublic($token)
    {
        $tokenRecord = PublicVerificationToken::where('token', $token)->firstOrFail();
        
        $document = null;
        $docType = $tokenRecord->document_type;

        if ($docType === 'invoice') {
            $document = Invoice::with(['reservation.roomType'])->findOrFail($tokenRecord->document_id);
        } elseif ($docType === 'meeting_reservation') {
            $document = MeetingReservation::with(['meetingRoom', 'meetingPackage'])->findOrFail($tokenRecord->document_id);
        } elseif ($docType === 'dining_reservation') {
            $document = DiningReservation::with(['diningPackage'])->findOrFail($tokenRecord->document_id);
        } else {
            abort(404);
        }

        return view('public.verify-receipt', [
            'document' => $document,
            'docType' => $docType,
            'isValid' => true,
        ]);
    }
}
