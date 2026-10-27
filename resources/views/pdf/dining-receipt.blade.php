<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bukti Reservasi Resto {{ $reservation->reservation_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; line-height: 1.4; margin: 0; padding: 15px; }
        .header { border-bottom: 2px solid #059669; padding-bottom: 10px; margin-bottom: 15px; }
        .logo-title { font-size: 18px; font-weight: bold; color: #059669; }
        .company-info { font-size: 10px; color: #666; }
        .invoice-title { font-size: 15px; font-weight: bold; text-align: right; color: #111; }
        .meta-table { width: 100%; margin-bottom: 15px; }
        .meta-table td { vertical-align: top; }
        .badge-wikrama { display: inline-block; background: #d1fae5; color: #065f46; font-size: 9px; font-weight: bold; padding: 2px 6px; border-radius: 4px; margin-top: 4px; border: 1px solid #a7f3d0; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .items-table th { background: #f3f4f6; text-align: left; padding: 6px 8px; border-bottom: 1px solid #ddd; font-size: 10px; text-transform: uppercase; }
        .items-table td { padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 10px; }
        .text-right { text-align: right; }
        .totals-table { width: 48%; margin-left: auto; margin-bottom: 15px; }
        .totals-table td { padding: 3px 6px; font-size: 10px; }
        .grand-total { font-size: 12px; font-weight: bold; color: #059669; border-top: 1px solid #ddd; }
        .qr-section { margin-top: 20px; border-top: 1px dashed #ccc; padding-top: 10px; }
        .footer { font-size: 9px; color: #999; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    @php
        $letterhead = \App\Models\HotelSetting::get('letterhead_portrait_path');
        $hotelName = \App\Models\HotelSetting::get('hotel_name', 'InSitu Hotel Syariah Garut');
        $hotelAddress = \App\Models\HotelSetting::get('hotel_address', 'Jl. Situ Indah No. 1, Garut, Jawa Barat');
        $hotelPhone = \App\Models\HotelSetting::get('hotel_phone', '0262-123456');
        $bankAccounts = collect(\App\Models\HotelSetting::get('bank_accounts', []))->where('is_active', true);
    @endphp

    @if($letterhead && file_exists(public_path('storage/' . $letterhead)))
        <div style="text-align: center; margin-bottom: 15px; border-bottom: 2px solid #059669; padding-bottom: 8px;">
            <img src="{{ public_path('storage/' . $letterhead) }}" style="width: 100%; max-height: 110px; object-fit: contain;">
        </div>
    @else
        <div class="header">
            <table style="width: 100%;">
                <tr>
                    <td>
                        <div class="logo-title">{{ strtoupper($hotelName) }}</div>
                        <div class="company-info">Restoran Halal & Fine Dining Syariah · {{ $hotelAddress }}</div>
                    </td>
                    <td class="invoice-title">
                        BUKTI RESERVASI RESTORAN<br>
                        <span style="font-size: 11px; font-weight: normal; color: #666;">No: {{ $reservation->reservation_number }}</span>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <table class="meta-table">
        <tr>
            <td style="width: 52%;">
                @if($reservation->group_name)
                    <strong>Rombongan / Instansi:</strong><br>
                    <span style="font-size: 12px; font-weight: bold; color: #111;">{{ $reservation->group_name }}</span><br>
                @endif
                <strong>Nama Pemesan:</strong> {{ $reservation->customer->name }} ({{ $reservation->customer->phone }})<br>
                @if($reservation->pic_name)
                    <strong>PIC Lapangan:</strong> {{ $reservation->pic_name }} {{ $reservation->pic_phone ? '('.$reservation->pic_phone.')' : '' }}<br>
                @endif
                @if($reservation->activity_link)
                    <strong>Link Kegiatan:</strong> <span style="color: #2563eb; word-break: break-all;">{{ $reservation->activity_link }}</span><br>
                @endif
                @if($reservation->is_wikrama_teachers)
                    <div class="badge-wikrama">✨ Panitia Guru Wikrama</div>
                @endif
            </td>
            <td style="width: 48%;" class="text-right">
                <strong>Tanggal Acara / Santap:</strong> {{ $reservation->reservation_date->format('d/m/Y') }}<br>
                <strong>Waktu:</strong> {{ $reservation->time_slot }} WIB<br>
                <strong>Area Meja:</strong> {{ $reservation->table_number_or_area }}<br>
                <strong>Jumlah Tamu:</strong> {{ $reservation->pax }} Orang
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Item / Paket Menu / Layanan</th>
                <th class="text-right" style="width: 15%;">Qty</th>
                <th class="text-right" style="width: 22%;">Tarif Satuan</th>
                <th class="text-right" style="width: 23%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>
                        @if($reservation->diningPackage)
                            {{ $reservation->diningPackage->name }}
                        @elseif($reservation->custom_menu_name)
                            {{ $reservation->custom_menu_name }}
                        @else
                            Pesanan Ala Carte
                        @endif
                    </strong>
                    @if($reservation->special_requests)
                        <br><span style="font-size: 9px; color: #666;">Catatan:<br>{!! nl2br(e($reservation->special_requests)) !!}</span>
                    @endif
                </td>
                <td class="text-right">{{ $reservation->pax }} Pax</td>
                <td class="text-right">{{ rupiah($reservation->price_per_pax) }}</td>
                <td class="text-right">{{ rupiah($reservation->subtotal) }}</td>
            </tr>

            @foreach($reservation->items as $item)
                <tr>
                    <td style="padding-left: 12px;">
                        • {{ $item->name }}
                    </td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{{ rupiah($item->unit_price) }}</td>
                    <td class="text-right">{{ rupiah($item->subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Subtotal Makanan:</td>
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
            <td>Pajak Restoran ({{ $reservation->apply_tax ? '10%' : '0%' }}):</td>
            <td class="text-right">{{ rupiah($reservation->tax_amount) }}</td>
        </tr>
        <tr class="grand-total">
            <td>Total Tagihan:</td>
            <td class="text-right">{{ rupiah($reservation->total_amount) }}</td>
        </tr>
        <tr>
            <td>Telah Dibayar:</td>
            <td class="text-right" style="color: #059669; font-weight: bold;">{{ rupiah($reservation->paid_amount) }}</td>
        </tr>
        <tr>
            <td>Sisa Saldo:</td>
            <td class="text-right" style="color: {{ $reservation->balance > 0 ? '#b91c1c' : '#059669' }}; font-weight: bold;">
                {{ rupiah($reservation->balance) }}
            </td>
        </tr>
    </table>

    @if($bankAccounts->isNotEmpty())
        <div style="margin-top: 12px; padding: 6px 10px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 9px;">
            <strong>Rekening Transfer Resmi Hotel:</strong>
            <div style="margin-top: 3px;">
                @foreach($bankAccounts as $bank)
                    <span style="display: inline-block; margin-right: 12px;">
                        • <strong>{{ $bank['bank_name'] }}</strong>: {{ $bank['account_number'] }} (a.n {{ $bank['account_holder'] }})
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <div class="qr-section">
        <table style="width: 100%;">
            <tr>
                <td style="width: 100px;">
                    <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" width="85" height="85" />
                </td>
                <td style="vertical-align: middle; font-size: 9px; color: #555;">
                    <strong>Verifikasi Dokumen Resmi:</strong><br>
                    Pindai QR code ini untuk memeriksa keaslian bukti pemesanan restoran pada sistem resmi InSitu Hotel Syariah Garut secara real-time.
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Selamat menikmati hidangan berkah di Restoran InSitu Hotel Syariah Garut.
    </div>
</body>
</html>
