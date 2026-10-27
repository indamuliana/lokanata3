<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk Resto #{{ $reservation->reservation_number }}</title>
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
            <div class="sub-header">Restoran Halal & Fine Dining Syariah</div>
            <div class="sub-header">{{ $hotelAddress }} · {{ $hotelPhone }}</div>
        @endif
    </div>

    <div class="divider"></div>

    <table>
        <tr>
            <td>No. Reservasi</td>
            <td class="text-right font-bold">{{ $reservation->reservation_number }}</td>
        </tr>
        @if($reservation->group_name)
            <tr>
                <td>Rombongan</td>
                <td class="text-right font-bold">{{ $reservation->group_name }}</td>
            </tr>
        @endif
        <tr>
            <td>Pemesan</td>
            <td class="text-right">{{ $reservation->customer->name }}</td>
        </tr>
        @if($reservation->pic_name)
            <tr>
                <td>PIC Lapangan</td>
                <td class="text-right">{{ $reservation->pic_name }}</td>
            </tr>
        @endif
        @if($reservation->is_wikrama_teachers)
            <tr>
                <td colspan="2" class="font-bold text-center" style="padding: 3px 0;">
                    [ ✨ PANITIA GURU WIKRAMA ]
                </td>
            </tr>
        @endif
        <tr>
            <td>Waktu Santap</td>
            <td class="text-right">{{ $reservation->reservation_date->format('d/m/Y') }}<br>{{ $reservation->time_slot }} WIB</td>
        </tr>
        <tr>
            <td>Area Meja</td>
            <td class="text-right">{{ $reservation->table_number_or_area }}</td>
        </tr>
        <tr>
            <td>Jumlah Tamu</td>
            <td class="text-right font-bold">{{ $reservation->pax }} Orang</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td colspan="2" class="font-bold">
                @if($reservation->diningPackage)
                    {{ $reservation->diningPackage->name }}
                @elseif($reservation->custom_menu_name)
                    {{ $reservation->custom_menu_name }}
                @else
                    Pesanan Ala Carte
                @endif
            </td>
        </tr>
        @if($reservation->special_requests)
            <tr>
                <td colspan="2" style="font-size: 9px; font-style: italic; padding: 2px 0 4px 0; color: #333;">
                    Catatan:<br>{!! nl2br(e($reservation->special_requests)) !!}
                </td>
            </tr>
        @endif
        <tr>
            <td>{{ $reservation->pax }} Pax x {{ rupiah($reservation->price_per_pax) }}</td>
            <td class="text-right">{{ rupiah($reservation->subtotal) }}</td>
        </tr>

        @if($reservation->items->count() > 0)
            @foreach($reservation->items as $item)
                <tr>
                    <td colspan="2" style="padding-top: 3px;">+ {{ $item->name }}</td>
                </tr>
                <tr>
                    <td style="padding-left: 8px;">{{ $item->quantity }} x {{ rupiah($item->unit_price) }}</td>
                    <td class="text-right">{{ rupiah($item->subtotal) }}</td>
                </tr>
            @endforeach
        @endif

        <tr style="border-top: 1px dotted #ccc;">
            <td style="padding-top: 4px;">Subtotal:</td>
            <td class="text-right" style="padding-top: 4px;">{{ rupiah($reservation->subtotal + $reservation->additional_charges) }}</td>
        </tr>
        @if($reservation->discount_amount > 0)
            <tr>
                <td>Diskon:</td>
                <td class="text-right">-{{ rupiah($reservation->discount_amount) }}</td>
            </tr>
        @endif
        <tr>
            <td>Pajak Resto ({{ $reservation->apply_tax ? '10%' : '0%' }}):</td>
            <td class="text-right">{{ rupiah($reservation->tax_amount) }}</td>
        </tr>
        <tr class="font-bold">
            <td>TOTAL:</td>
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
        *** Selamat Menikmati Hidangan Halal ***<br>
        Jazakumullah Khairan Katsiran.
    </div>
</body>
</html>
