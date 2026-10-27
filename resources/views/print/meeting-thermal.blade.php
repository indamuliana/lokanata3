<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk Meeting #{{ $reservation->reservation_number }}</title>
    <style>
        @page { size: 80mm auto; margin: 0; }
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
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .header-title { font-size: 13px; font-weight: bold; }
        .sub-header { font-size: 9px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .qr-box { margin: 8px auto; text-align: center; }
        .btn-print { padding: 6px 12px; background: #059669; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; }
        @media print { .no-print { display: none !important; } body { padding: 0; width: 100%; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 10px; text-align: center;">
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
        <div class="sub-header">Struk Sewa Ruang Meeting / Acara</div>
    </div>

    <div class="divider"></div>

    <table>
        <tr>
            <td>No. Booking</td>
            <td class="text-right font-bold">{{ $reservation->reservation_number }}</td>
        </tr>
        <tr>
            <td>Acara</td>
            <td class="text-right">{{ $reservation->event_name }}</td>
        </tr>
        <tr>
            <td>Pemesan</td>
            <td class="text-right">{{ $reservation->customer->name }}</td>
        </tr>
        <tr>
            <td>Ruangan</td>
            <td class="text-right">{{ $reservation->meetingRoom->name }}</td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td class="text-right">{{ $reservation->event_date->format('d/m/Y') }}<br>{{ $reservation->start_time }} - {{ $reservation->end_time }}</td>
        </tr>
        <tr>
            <td>Peserta</td>
            <td class="text-right">{{ $reservation->pax }} Orang</td>
        </tr>
        @if($reservation->facility_notes)
            <tr>
                <td colspan="2" style="font-size: 9px; padding-top: 4px;">
                    <strong>Fasilitas:</strong><br>{{ $reservation->facility_notes }}
                </td>
            </tr>
        @endif
    </table>

    @if($reservation->items && $reservation->items->count() > 0)
        <div class="divider"></div>
        <div style="font-weight: bold; font-size: 9px; margin-bottom: 2px;">Layanan Tambahan:</div>
        <table>
            @foreach($reservation->items as $it)
                <tr>
                    <td>{{ $it->name }}<br><span style="font-size: 8px; color: #555;">{{ $it->quantity }}x @ {{ rupiah($it->unit_price) }}</span></td>
                    <td class="text-right font-bold">{{ rupiah($it->subtotal) }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <div class="divider"></div>

    <table>
        <tr>
            <td>Sewa / Paket:</td>
            <td class="text-right">{{ rupiah($reservation->subtotal) }}</td>
        </tr>
        @if($reservation->additional_charges > 0)
            <tr>
                <td>Layanan Tambahan:</td>
                <td class="text-right">{{ rupiah($reservation->additional_charges) }}</td>
            </tr>
        @endif
        @if($reservation->discount_amount > 0)
            <tr>
                <td>Diskon:</td>
                <td class="text-right">-{{ rupiah($reservation->discount_amount) }}</td>
            </tr>
        @endif
        <tr>
            <td>Pajak (10%):</td>
            <td class="text-right">{{ rupiah($reservation->tax_amount) }}</td>
        </tr>
        <tr class="font-bold">
            <td>TOTAL TAGIHAN:</td>
            <td class="text-right">{{ rupiah($reservation->total_amount) }}</td>
        </tr>
        <tr>
            <td>Telah Dibayar:</td>
            <td class="text-right font-bold">{{ rupiah($reservation->paid_amount) }}</td>
        </tr>
        <tr class="font-bold">
            <td>SISA TAGIHAN:</td>
            <td class="text-right">{{ rupiah($reservation->balance) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="qr-box">
        {!! $qrCodeSvg !!}
        <div style="font-size: 8px; margin-top: 3px;">Scan QR untuk Verifikasi Keaslian</div>
    </div>

    <div class="text-center" style="font-size: 9px; margin-top: 6px;">
        *** Jazakumullah Khairan ***<br>
        Simpan struk ini sebagai bukti transaksi sah.
    </div>
</body>
</html>
