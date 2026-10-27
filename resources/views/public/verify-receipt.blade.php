@php
    $inv = $invoice ?? ($docType === 'invoice' ? $document : null);
    $meeting = ($docType ?? '') === 'meeting_reservation' ? $document : null;
    $dining = ($docType ?? '') === 'dining_reservation' ? $document : null;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen Resmi - InSitu Hotel Syariah</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        <!-- Header -->
        <div class="bg-emerald-700 text-white p-6 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-emerald-600 mb-3">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
            </div>
            <h1 class="text-xl font-bold tracking-tight">InSitu Hotel Syariah Garut</h1>
            <p class="text-xs text-emerald-100 mt-1">Sistem Verifikasi Dokumen & Bukti Bayar Resmi</p>
        </div>

        <!-- Verification Status Badge -->
        <div class="p-6">
            <div class="flex items-center justify-center gap-2 p-3 bg-emerald-50 border border-emerald-200 rounded-xl mb-6">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-emerald-800 font-semibold text-sm">Dokumen Terverifikasi Sah dan Asli</span>
            </div>

            <!-- Details Table (Strictly no PII / no phone / no NIK) -->
            <div class="space-y-3 text-sm">
                @if($inv)
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Nomor Invoice</span>
                        <span class="font-bold text-gray-800">{{ $inv->invoice_number }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Tanggal Terbit</span>
                        <span class="text-gray-700">{{ $inv->created_at->format('d M Y, H:i') }} WIB</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Kode Reservasi</span>
                        <span class="font-medium text-gray-800">#{{ $inv->reservation->reservation_number }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Tipe Kamar</span>
                        <span class="font-medium text-gray-800">{{ $inv->reservation->roomType->name }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Periode Menginap</span>
                        <span class="text-gray-700">{{ $inv->reservation->check_in_date->format('d/m/Y') }} - {{ $inv->reservation->check_out_date->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Status Pembayaran</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $inv->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $inv->status === 'paid' ? 'LUNAS' : ($inv->status === 'partially_paid' ? 'SEBAGIAN' : 'BELUM BAYAR') }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Total Transaksi</span>
                        <span class="font-bold text-gray-900 text-base">{{ rupiah($inv->total_amount) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-500">Jumlah Dibayar</span>
                        <span class="font-bold text-emerald-700 text-base">{{ rupiah($inv->paid_amount) }}</span>
                    </div>
                @elseif($meeting)
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Nomor Reservasi</span>
                        <span class="font-bold text-gray-800">{{ $meeting->reservation_number }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Nama Acara</span>
                        <span class="font-medium text-gray-800">{{ $meeting->event_name }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Ruangan</span>
                        <span class="font-medium text-gray-800">{{ $meeting->meetingRoom->name }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Waktu Acara</span>
                        <span class="text-gray-700">{{ $meeting->event_date->format('d M Y') }} ({{ $meeting->start_time }} - {{ $meeting->end_time }})</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Status Pembayaran</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $meeting->payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ strtoupper($meeting->payment_status) }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Total Tagihan</span>
                        <span class="font-bold text-gray-900 text-base">{{ rupiah($meeting->total_amount) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-500">Jumlah Dibayar</span>
                        <span class="font-bold text-emerald-700 text-base">{{ rupiah($meeting->paid_amount) }}</span>
                    </div>
                @elseif($dining)
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Nomor Reservasi</span>
                        <span class="font-bold text-gray-800">{{ $dining->reservation_number }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Paket Resto</span>
                        <span class="font-medium text-gray-800">{{ $dining->diningPackage ? $dining->diningPackage->name : 'Ala Carte' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Area Meja</span>
                        <span class="font-medium text-gray-800">{{ $dining->table_number_or_area }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Waktu Reservasi</span>
                        <span class="text-gray-700">{{ $dining->reservation_date->format('d M Y') }} - {{ $dining->time_slot }} WIB</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500">Total Tagihan</span>
                        <span class="font-bold text-gray-900 text-base">{{ rupiah($dining->total_amount) }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-500">Jumlah Dibayar</span>
                        <span class="font-bold text-emerald-700 text-base">{{ rupiah($dining->paid_amount) }}</span>
                    </div>
                @endif
            </div>

            <!-- Syariah & Security Note -->
            <div class="mt-6 p-4 bg-gray-50 rounded-xl text-xs text-gray-500 space-y-1">
                <p class="font-semibold text-gray-600">Catatan Privasi & Keamanan:</p>
                <p>Data pribadi tamu (NIK, No. Telepon, Alamat) dilindungi dan tidak ditampilkan pada halaman publik ini sesuai kebijakan perlindungan data InSitu Hotel Syariah.</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} InSitu Hotel Syariah Garut · Layanan Ramah, Nyaman, dan Berkah
        </div>
    </div>
</body>
</html>
