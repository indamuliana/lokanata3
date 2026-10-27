<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Riwayat Pesanan - {{ $settings['hotel_name'] ?? 'InSitu Hotel Syariah' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 1.2cm 1.5cm 1.2cm 1.5cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.4;
        }
        /* Kop Surat Hotel */
        .kop-table {
            width: 100%;
            border-bottom: 2px solid #059669;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .kop-logo {
            width: 70px;
            vertical-align: middle;
        }
        .kop-info {
            vertical-align: middle;
            padding-left: 10px;
        }
        .hotel-name {
            font-size: 16px;
            font-weight: bold;
            color: #065f46;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .hotel-meta {
            font-size: 9px;
            color: #4b5563;
            margin-top: 2px;
        }
        .report-title-box {
            text-align: right;
            vertical-align: middle;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
            margin: 0;
            text-transform: uppercase;
        }
        .report-period {
            font-size: 9px;
            color: #059669;
            font-weight: bold;
            margin-top: 3px;
        }

        /* Summary Cards */
        .summary-box {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .summary-cell {
            padding: 8px 12px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            text-align: left;
        }
        .summary-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: bold;
        }
        .summary-value {
            font-size: 12px;
            font-weight: bold;
            color: #111827;
            margin-top: 2px;
        }
        .summary-green {
            color: #047857;
        }

        /* Tabel Data Transaksi */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        table.data-table th {
            background-color: #f3f4f6;
            color: #374151;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 8px;
            padding: 6px 6px;
            border: 1px solid #d1d5db;
            text-align: left;
        }
        table.data-table td {
            padding: 5px 6px;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f9fafb;
        }
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-room { background-color: #e0e7ff; color: #3730a3; }
        .badge-meeting { background-color: #fef3c7; color: #92400e; }
        .badge-dining { background-color: #ccfbf1; color: #115e59; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Tanda Tangan */
        .signature-table {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .sig-box {
            width: 250px;
            text-align: center;
        }
        .sig-line {
            margin-top: 50px;
            border-bottom: 1px solid #374151;
            font-weight: bold;
        }
        .footer-note {
            margin-top: 15px;
            font-size: 8px;
            color: #9ca3af;
            text-align: right;
            border-top: 1px solid #e5e7eb;
            padding-top: 4px;
        }
    </style>
</head>
<body>

    <!-- Kop Surat Resmi -->
    <table class="kop-table">
        <tr>
            <td class="kop-info">
                <div class="hotel-name">{{ $settings['hotel_name'] ?? 'InSitu Hotel Syariah' }}</div>
                <div class="hotel-meta">
                    {{ $settings['hotel_address'] ?? 'Garut, Jawa Barat, Indonesia' }} | Telp/WA: {{ $settings['hotel_phone'] ?? '08123456789' }}
                </div>
            </td>
            <td class="report-title-box">
                <div class="report-title">Laporan Riwayat Pesanan</div>
                <div class="report-period">Periode: {{ $startDate }} - {{ $endDate }}</div>
            </td>
        </tr>
    </table>

    <!-- Ringkasan Eksekutif -->
    <table class="summary-box">
        <tr>
            <td class="summary-cell">
                <div class="summary-label">Total Transaksi</div>
                <div class="summary-value">{{ count($items) }} Pesanan</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Total Omzet / Pendapatan</div>
                <div class="summary-value summary-green">{{ rupiah($totalRevenue) }}</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Total Terbayar (Lunas/DP)</div>
                <div class="summary-value">{{ rupiah($totalPaid) }}</div>
            </td>
            <td class="summary-cell">
                <div class="summary-label">Sisa Tagihan Tertunggak</div>
                <div class="summary-value" style="color: {{ $totalBalance > 0 ? '#b91c1c' : '#4b5563' }};">
                    {{ rupiah($totalBalance) }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Tabel Data Transaksi -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20px;" class="text-center">No</th>
                <th style="width: 70px;">Unit</th>
                <th style="width: 65px;">Tanggal</th>
                <th style="width: 75px;">No. Booking</th>
                <th style="width: 120px;">Nama Tamu</th>
                <th style="width: 75px;">Kontak</th>
                <th>Rincian Pesanan</th>
                <th style="width: 80px;" class="text-right">Total Tagihan</th>
                <th style="width: 75px;" class="text-right">Terbayar</th>
                <th style="width: 65px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>
                        @if($row['unit_code'] === 'ROOM')
                            <span class="badge badge-room">Kamar</span>
                        @elseif($row['unit_code'] === 'MEETING')
                            <span class="badge badge-meeting">Meeting</span>
                        @else
                            <span class="badge badge-dining">Dining</span>
                        @endif
                    </td>
                    <td>{{ $row['date'] }}</td>
                    <td style="font-family: monospace; font-weight: bold;">{{ $row['code'] }}</td>
                    <td><strong>{{ $row['customer'] }}</strong></td>
                    <td>{{ $row['phone'] }}</td>
                    <td>{{ $row['detail'] }}</td>
                    <td class="text-right font-bold">{{ rupiah($row['total']) }}</td>
                    <td class="text-right">{{ rupiah($row['paid']) }}</td>
                    <td class="text-center">
                        <span style="font-size: 8px; font-weight: bold;">
                            {{ ucfirst(str_replace('_', ' ', $row['status'])) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px; color: #9ca3af;">
                        Tidak ada catatan riwayat transaksi pada rentang periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(count($items) > 0)
            <tfoot>
                <tr style="background-color: #f3f4f6; font-weight: bold;">
                    <td colspan="7" class="text-right" style="padding: 6px;">GRAND TOTAL:</td>
                    <td class="text-right" style="color: #047857;">{{ rupiah($totalRevenue) }}</td>
                    <td class="text-right">{{ rupiah($totalPaid) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Tanda Tangan Manajerial -->
    <table class="signature-table">
        <tr>
            <td></td>
            <td class="sig-box">
                <div>Garut, {{ now()->format('d M Y') }}</div>
                <div style="font-size: 8px; color: #6b7280; margin-top: 2px;">Penanggung Jawab / General Manager,</div>
                <div class="sig-line">{{ Auth::user()->name ?? 'Manajer Operasional' }}</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen dicetak otomatis dari Sistem Manajemen Hotel Syariah InSitu pada {{ $printedAt }} WIB.
    </div>

</body>
</html>
