<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
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
                        INVOICE / KWITANSI<br>
                        <span style="font-size: 12px; font-weight: normal; color: #666;">No: {{ $invoice->invoice_number }}</span>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <strong>Kepada Yth:</strong><br>
                {{ $reservation->customer->name }}<br>
                Telp: +{{ $reservation->customer->phone }}
            </td>
            <td style="width: 50%;" class="text-right">
                <strong>No. Reservasi:</strong> {{ $reservation->reservation_number }}<br>
                <strong>Tanggal Terbit:</strong> {{ $invoice->created_at->format('d/m/Y') }}<br>
                <strong>Periode Menginap:</strong> {{ $reservation->check_in_date->format('d/m/Y') }} - {{ $reservation->check_out_date->format('d/m/Y') }} ({{ $reservation->nights_count }} Malam)
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>Deskripsi Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Tarif Satuan</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    Sewa Kamar: {{ $reservation->roomType->name }}
                    @if($reservation->allocatedRooms->isNotEmpty())
                        (Kamar No: {{ $reservation->allocatedRooms->pluck('room.room_number')->join(', ') }})
                    @endif
                </td>
                <td class="text-right">{{ $reservation->nights_count }} Malam</td>
                <td class="text-right">{{ rupiah($reservation->subtotal / $reservation->nights_count) }}</td>
                <td class="text-right">{{ rupiah($reservation->subtotal) }}</td>
            </tr>
            @foreach($reservation->serviceItems as $svc)
                <tr>
                    <td>{{ $svc->item_name }}</td>
                    <td class="text-right">{{ $svc->quantity }}</td>
                    <td class="text-right">{{ rupiah($svc->unit_price) }}</td>
                    <td class="text-right">{{ rupiah($svc->subtotal) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Subtotal:</td>
            <td class="text-right">{{ rupiah($invoice->subtotal) }}</td>
        </tr>
        @if($invoice->discount_amount > 0)
            <tr>
                <td>Diskon:</td>
                <td class="text-right">-{{ rupiah($invoice->discount_amount) }}</td>
            </tr>
        @endif
        <tr>
            <td>Pajak Daerah (10%):</td>
            <td class="text-right">{{ rupiah($invoice->tax_amount) }}</td>
        </tr>
        <tr class="grand-total">
            <td>Total Tagihan:</td>
            <td class="text-right">{{ rupiah($invoice->total_amount) }}</td>
        </tr>
        <tr>
            <td>Total Dibayar:</td>
            <td class="text-right" style="color: #059669; font-weight: bold;">{{ rupiah($invoice->paid_amount) }}</td>
        </tr>
        <tr>
            <td>Sisa Saldo:</td>
            <td class="text-right" style="color: {{ $invoice->balance > 0 ? '#b91c1c' : '#059669' }}; font-weight: bold;">
                {{ rupiah($invoice->balance) }}
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
                    Pindai QR code di samping menggunakan kamera smartphone untuk memverifikasi keaslian bukti pembayaran ini pada sistem resmi InSitu Hotel Syariah Garut secara real-time.
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Terima kasih atas kunjungan Anda di InSitu Hotel Syariah Garut · Dokumen ini sah dan diterbitkan secara digital.
    </div>
</body>
</html>
