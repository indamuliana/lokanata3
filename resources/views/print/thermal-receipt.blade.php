<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk #{{ $invoice->invoice_number }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            color: #000;
            background: #fff;
            margin: 0 auto;
            padding: 8px 12px;
            width: 76mm;
            box-sizing: border-box;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .double-divider {
            border-top: 1px double #000;
            margin: 6px 0;
        }
        .header-title {
            font-size: 13px;
            font-weight: bold;
        }
        .sub-header {
            font-size: 9px;
            margin-bottom: 4px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 2px 0;
            vertical-align: top;
        }
        .qr-box {
            margin: 8px auto;
            text-align: center;
        }
        .qr-box svg {
            display: inline-block;
        }
        .no-print {
            margin-bottom: 10px;
            text-align: center;
        }
        .btn-print {
            padding: 6px 12px;
            background: #059669;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Struk (Thermal 80mm)</button>
    </div>

    @php
        $thermalKop = \App\Models\HotelSetting::get('letterhead_thermal_path');
        $hotelName = \App\Models\HotelSetting::get('hotel_name', 'InSitu Hotel Syariah Garut');
        $hotelAddress = \App\Models\HotelSetting::get('hotel_address', 'Jl. Situ Indah No. 1, Garut');
        $hotelPhone = \App\Models\HotelSetting::get('hotel_phone', '0262-123456');
    @endphp

    <div class="text-center">
        @if($thermalKop && file_exists(public_path('storage/' . $thermalKop)))
            <img src="{{ asset('storage/' . $thermalKop) }}" style="max-width: 100%; height: auto; margin-bottom: 4px;">
        @else
            <div class="header-title">{{ strtoupper($hotelName) }}</div>
            <div class="sub-header">{{ $hotelAddress }} · {{ $hotelPhone }}</div>
        @endif
        <div class="sub-header">Bukti Pembayaran / Struk Kasir</div>
    </div>

    <div class="divider"></div>

    <table>
        <tr>
            <td>No. Bukti</td>
            <td class="text-right font-bold">{{ $invoice->invoice_number }}</td>
        </tr>
        <tr>
            <td>Reservasi</td>
            <td class="text-right">#{{ $reservation->reservation_number }}</td>
        </tr>
        <tr>
            <td>Tamu</td>
            <td class="text-right">{{ $reservation->customer->name }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td class="text-right">{{ $invoice->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Check In</td>
            <td class="text-right">{{ $reservation->check_in_date->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Check Out</td>
            <td class="text-right">{{ $reservation->check_out_date->format('d/m/Y') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Items -->
    <table>
        <thead>
            <tr>
                <td class="font-bold">Item</td>
                <td class="text-right font-bold">Total</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ $reservation->roomType->name }}<br>
                    <span style="font-size: 9px;">({{ $reservation->nights_count }} Malam)</span>
                    @if($reservation->allocatedRooms->isNotEmpty())
                        <br><span style="font-size: 9px;">Kamar: {{ $reservation->allocatedRooms->pluck('room.room_number')->join(', ') }}</span>
                    @endif
                </td>
                <td class="text-right">{{ rupiah($reservation->subtotal) }}</td>
            </tr>
            @foreach($reservation->serviceItems as $item)
                <tr>
                    <td>
                        {{ $item->item_name }}<br>
                        <span style="font-size: 9px;">{{ $item->quantity }} x {{ rupiah($item->unit_price) }}</span>
                    </td>
                    <td class="text-right">{{ rupiah($item->subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <!-- Totals -->
    <table>
        <tr>
            <td>Subtotal</td>
            <td class="text-right">{{ rupiah($invoice->subtotal) }}</td>
        </tr>
        @if($invoice->discount_amount > 0)
            <tr>
                <td>Diskon</td>
                <td class="text-right">-{{ rupiah($invoice->discount_amount) }}</td>
            </tr>
        @endif
        <tr>
            <td>Pajak (10%)</td>
            <td class="text-right">{{ rupiah($invoice->tax_amount) }}</td>
        </tr>
        <tr class="font-bold">
            <td>TOTAL</td>
            <td class="text-right">{{ rupiah($invoice->total_amount) }}</td>
        </tr>
        <tr>
            <td>Telah Dibayar</td>
            <td class="text-right font-bold">{{ rupiah($invoice->paid_amount) }}</td>
        </tr>
        <tr class="font-bold">
            <td>SISA TAGIHAN</td>
            <td class="text-right">{{ rupiah($invoice->balance) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <!-- Payments history breakdown -->
    @if($reservation->payments->isNotEmpty())
        <div style="font-size: 9px;">
            <div class="font-bold">Rincian Pembayaran:</div>
            @foreach($reservation->payments as $p)
                <table>
                    <tr>
                        <td>{{ $p->created_at->format('d/m H:i') }} - {{ strtoupper($p->payment_method) }}</td>
                        <td class="text-right">{{ $p->is_refund ? '-' : '' }}{{ rupiah($p->amount) }}</td>
                    </tr>
                </table>
            @endforeach
        </div>
        <div class="divider"></div>
    @endif

    @php
        $bankAccounts = collect(\App\Models\HotelSetting::get('bank_accounts', []))->where('is_active', true);
    @endphp
    @if($bankAccounts->isNotEmpty() && $invoice->balance > 0)
        <div style="font-size: 9px; margin-top: 4px;">
            <div class="font-bold">Info Rekening Transfer:</div>
            @foreach($bankAccounts as $b)
                <div>• {{ $b['bank_name'] }}: {{ $b['account_number'] }} ({{ $b['account_holder'] }})</div>
            @endforeach
        </div>
        <div class="divider"></div>
    @endif

    <div class="qr-box">
        {!! $qrCodeSvg !!}
        <div style="font-size: 8px; margin-top: 3px;">Scan QR untuk Verifikasi Keaslian</div>
    </div>

    <div class="text-center" style="font-size: 9px; margin-top: 6px;">
        *** Jazakumullah Khairan ***<br>
        Simpan struk ini sebagai bukti pembayaran yang sah.
    </div>
</body>
</html>
