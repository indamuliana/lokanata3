<?php

namespace App\Http\Controllers;

use App\Models\DiningReservation;
use App\Models\HotelSetting;
use App\Models\MeetingReservation;
use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    /**
     * Gather transactions based on unit and date range filters
     */
    private function getTransactions(Request $request): array
    {
        $unit = $request->query('unit', 'all');
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $isHistoryOnly = $request->boolean('history_only', false);

        $today = now()->toDateString();
        $items = collect();

        // 1. Kamar Hotel
        if (in_array($unit, ['all', 'room'])) {
            $q = Reservation::with(['customer', 'roomType']);
            if ($startDate && $endDate) {
                $q->whereDate('check_in_date', '>=', $startDate)->whereDate('check_in_date', '<=', $endDate);
            }
            if ($isHistoryOnly) {
                $q->where(fn ($sub) => $sub->whereIn('status', ['checked_out', 'cancelled', 'expired'])->orWhereDate('check_out_date', '<', $today));
            }

            foreach ($q->orderBy('check_in_date', 'desc')->get() as $r) {
                $items->push([
                    'unit_type' => 'Kamar Hotel',
                    'unit_code' => 'ROOM',
                    'date' => $r->check_in_date ? $r->check_in_date->format('d/m/Y') : '-',
                    'raw_date' => $r->check_in_date?->toDateString(),
                    'code' => $r->reservation_number,
                    'customer' => $r->customer->name ?? '-',
                    'phone' => $r->customer->phone ?? '-',
                    'detail' => ($r->roomType?->name ?? 'Kamar') . ' (' . $r->nights_count . ' Malam)',
                    'total' => (int) $r->total_amount,
                    'paid' => (int) $r->paid_amount,
                    'balance' => (int) $r->balance,
                    'status' => $r->status,
                    'payment_status' => $r->payment_status,
                ]);
            }
        }

        // 2. Ruang Meeting
        if (in_array($unit, ['all', 'meeting'])) {
            $q = MeetingReservation::with(['customer', 'meetingRoom', 'meetingPackage']);
            if ($startDate && $endDate) {
                $q->whereDate('event_date', '>=', $startDate)->whereDate('event_date', '<=', $endDate);
            }
            if ($isHistoryOnly) {
                $q->where(fn ($sub) => $sub->whereIn('status', ['completed', 'cancelled'])->orWhereDate('event_date', '<', $today));
            }

            foreach ($q->orderBy('event_date', 'desc')->get() as $r) {
                $items->push([
                    'unit_type' => 'Ruang Meeting',
                    'unit_code' => 'MEETING',
                    'date' => $r->event_date ? $r->event_date->format('d/m/Y') : '-',
                    'raw_date' => $r->event_date?->toDateString(),
                    'code' => $r->reservation_number,
                    'customer' => $r->customer->name ?? '-',
                    'phone' => $r->customer->phone ?? '-',
                    'detail' => ($r->meetingRoom?->name ?? 'Ruang Meeting') . ' - ' . ($r->event_name ?: 'Acara'),
                    'total' => (int) $r->total_amount,
                    'paid' => (int) $r->paid_amount,
                    'balance' => max(0, (int)$r->total_amount - (int)$r->paid_amount),
                    'status' => $r->status,
                    'payment_status' => $r->payment_status,
                ]);
            }
        }

        // 3. Fine Dining
        if (in_array($unit, ['all', 'dining'])) {
            $q = DiningReservation::with(['customer', 'diningPackage']);
            if ($startDate && $endDate) {
                $q->whereDate('reservation_date', '>=', $startDate)->whereDate('reservation_date', '<=', $endDate);
            }
            if ($isHistoryOnly) {
                $q->where(fn ($sub) => $sub->whereIn('status', ['completed', 'cancelled'])->orWhereDate('reservation_date', '<', $today));
            }

            foreach ($q->orderBy('reservation_date', 'desc')->get() as $r) {
                $items->push([
                    'unit_type' => 'Fine Dining',
                    'unit_code' => 'DINING',
                    'date' => $r->reservation_date ? $r->reservation_date->format('d/m/Y') : '-',
                    'raw_date' => $r->reservation_date?->toDateString(),
                    'code' => $r->reservation_number,
                    'customer' => $r->customer->name ?? '-',
                    'phone' => $r->customer->phone ?? '-',
                    'detail' => ($r->diningPackage?->name ?? 'Paket Dining') . ' (' . $r->guests_count . ' Pax)',
                    'total' => (int) $r->total_amount,
                    'paid' => (int) $r->paid_amount,
                    'balance' => max(0, (int)$r->total_amount - (int)$r->paid_amount),
                    'status' => $r->status,
                    'payment_status' => $r->payment_status,
                ]);
            }
        }

        return $items->sortByDesc('raw_date')->values()->all();
    }

    /**
     * Export to CSV (Excel compatible with UTF-8 BOM)
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $unit = $request->query('unit', 'all');
        $fileName = "Laporan_Riwayat_{$unit}_{$startDate}_{$endDate}.csv";

        $data = $this->getTransactions($request);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($data) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM so Microsoft Excel renders accented characters and symbols properly
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, ['No', 'Unit Layanan', 'Tanggal', 'No. Transaksi', 'Nama Tamu', 'No. Kontak', 'Rincian Pesanan', 'Total Tagihan (Rp)', 'Terbayar (Rp)', 'Sisa Tagihan (Rp)', 'Status Pesanan', 'Status Bayar']);

            $no = 1;
            foreach ($data as $row) {
                fputcsv($handle, [
                    $no++,
                    $row['unit_type'],
                    $row['date'],
                    $row['code'],
                    $row['customer'],
                    $row['phone'],
                    $row['detail'],
                    $row['total'],
                    $row['paid'],
                    $row['balance'],
                    ucfirst(str_replace('_', ' ', $row['status'])),
                    ucfirst(str_replace('_', ' ', $row['payment_status'])),
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export to PDF Report (A4 Landscape with Hotel Letterhead)
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $unit = $request->query('unit', 'all');

        $data = $this->getTransactions($request);
        $settings = [
            'hotel_name' => HotelSetting::get('hotel_name', 'InSitu Hotel Syariah Garut'),
            'hotel_address' => HotelSetting::get('hotel_address', 'Garut, Jawa Barat, Indonesia'),
            'hotel_phone' => HotelSetting::get('hotel_phone', '08123456789'),
        ];

        $totalRevenue = array_sum(array_column($data, 'total'));
        $totalPaid = array_sum(array_column($data, 'paid'));
        $totalBalance = array_sum(array_column($data, 'balance'));

        $pdf = Pdf::loadView('reports.pdf.history', [
            'items' => $data,
            'settings' => $settings,
            'startDate' => Carbon::parse($startDate)->format('d M Y'),
            'endDate' => Carbon::parse($endDate)->format('d M Y'),
            'unit' => $unit,
            'totalRevenue' => $totalRevenue,
            'totalPaid' => $totalPaid,
            'totalBalance' => $totalBalance,
            'printedAt' => now()->format('d M Y H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("Laporan_Riwayat_{$unit}_{$startDate}_{$endDate}.pdf");
    }
}
