<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice Meeting {{ $reservation->reservation_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; line-height: 1.5; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #059669; padding-bottom: 12px; margin-bottom: 20px; }
        .logo-title { font-size: 20px; font-weight: bold; color: #059669; }
        .company-info { font-size: 10px; color: #666; }
        .invoice-title { font-size: 16px; font-weight: bold; text-align: right; color: #111; }
        .meta-table { width: 100%; margin-bottom: 20px; }
        .meta-table td { vertical-align: top; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #f3f4f6; text-align: left; padding: 8px; border-bottom: 1px solid #ddd; font-size: 11px; }
        .items-table td { padding: 8px; border-bottom: 1px solid #eee; font-size: 11px; }
        .text-right { text-align: right; }
        .totals-table { width: 45%; margin-left: auto; margin-bottom: 20px; }
        .totals-table td { padding: 4px 8px; font-size: 11px; }
        .grand-total { font-size: 13px; font-weight: bold; color: #059669; border-top: 1px solid #ddd; }
        .qr-section { margin-top: 30px; border-top: 1px dashed #ccc; padding-top: 15px; }
        .footer { font-size: 9px; color: #999; text-align: center; margin-top: 30px; }
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
            <img src="{{ public_path('storage/' . $letterhead) }}" style="width: 100%; max-height: 120px; object-fit: contain;">
        </div>
    @else
        <div class="header">
            <table style="width: 100%;">
                <tr>
                    <td>
                        <div class="logo-title">{{ strtoupper($hotelName) }}</div>
                        <div class="company-info">{{ $hotelAddress }} · Telp: {{ $hotelPhone }}</div>
                    </td>
                    <td class="invoice-title">
                        INVOICE MEETING & EVENT<br>
                        <span style="font-size: 12px; font-weight: normal; color: #666;">No: {{ $reservation->reservation_number }}</span>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <strong>Penyelenggara / PIC:</strong><br>
                {{ $reservation->customer->name }}<br>
                @if($reservation->customer->company)
                    Instansi: {{ $reservation->customer->company }}<br>
                @endif
                Telp: +{{ $reservation->customer->phone }}
            </td>
            <td style="width: 50%;" class="text-right">
                <strong>Nama Acara:</strong> {{ $reservation->event_name }}<br>
                <strong>Ruangan:</strong> {{ $reservation->meetingRoom->name }}<br>
                <strong>Tanggal Acara:</strong> {{ $reservation->event_date->format('d/m/Y') }}<br>
                <strong>Waktu:</strong> {{ $reservation->start_time }} - {{ $reservation->end_time }} WIB ({{ $reservation->pax }} Orang)
                @if($reservation->facility_notes)
                    <div style="margin-top: 6px; font-size: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 4px 8px; border-radius: 4px; text-align: left;">
                        <strong>Kebutuhan Fasilitas & Tata Letak:</strong><br>{{ $reservation->facility_notes }}
                    </div>
                @endif
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Deskripsi Layanan</th>
                <th class="text-right">Skema / Paket</th>
                <th class="text-right">Tarif Dasar</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    Sewa Ruang: {{ $reservation->meetingRoom->name }}
                    @if($reservation->meetingPackage)
                        <br><span style="font-size: 10px; color: #666;">Paket: {{ $reservation->meetingPackage->name }}</span>
                    @endif
                </td>
                <td class="text-right">{{ ucfirst(str_replace('_', ' ', $reservation->pricing_type)) }}</td>
                <td class="text-right">{{ rupiah($reservation->subtotal) }}</td>
                <td class="text-right">{{ rupiah($reservation->subtotal) }}</td>
            </tr>
            @if($reservation->items && $reservation->items->count() > 0)
                @foreach($reservation->items as $it)
                    <tr>
                        <td>
                            <strong>Layanan Tambahan:</strong> {{ $it->name }}
                        </td>
                        <td class="text-right">{{ $it->quantity }}x</td>
                        <td class="text-right">{{ rupiah($it->unit_price) }}</td>
                        <td class="text-right">{{ rupiah($it->subtotal) }}</td>
                    </tr>
                @endforeach
            @elseif($reservation->additional_charges > 0)
                <tr>
                    <td colspan="3">Fasilitas / Layanan Tambahan</td>
                    <td class="text-right">{{ rupiah($reservation->additional_charges) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Subtotal:</td>
            <td class="text-right">{{ rupiah($reservation->subtotal + $reservation->additional_charges) }}</td>
        </tr>
        @if($reservation->discount_amount > 0)
            <tr>
                <td>Diskon:</td>
                <td class="text-right">-{{ rupiah($reservation->discount_amount) }}</td>
            </tr>
        @endif
        <tr>
            <td>Pajak Daerah (10%):</td>
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
        <div style="margin-top: 15px; padding: 8px 12px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 10px;">
            <strong>Rekening Transfer Resmi Hotel:</strong>
            <div style="margin-top: 4px;">
                @foreach($bankAccounts as $bank)
                    <span style="display: inline-block; margin-right: 15px;">
                        • <strong>{{ $bank['bank_name'] }}</strong>: {{ $bank['account_number'] }} (a.n {{ $bank['account_holder'] }})
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <div class="qr-section">
        <table style="width: 100%;">
            <tr>
                <td style="width: 130px;">
                    <img src="data:image/svg+xml;base64,{{ $qrCodeBase64 }}" width="95" height="95" />
                </td>
                <td style="vertical-align: middle; font-size: 10px; color: #555;">
                    <strong>Verifikasi Dokumen Resmi:</strong><br>
                    Pindai QR code ini untuk memeriksa keaslian bukti pemesanan ruang meeting pada sistem resmi InSitu Hotel Syariah Garut secara real-time.
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Terima kasih atas kepercayaan Anda menyelenggarakan kegiatan di InSitu Hotel Syariah Garut.
    </div>
</body>
</html>
