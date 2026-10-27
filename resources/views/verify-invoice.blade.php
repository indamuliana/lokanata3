<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Invoice - Hotel Insitu Syariah</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface-light min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-8 text-center border-t-8 border-primary-dark">
        <div class="w-20 h-20 mx-auto bg-surface-soft rounded-full flex items-center justify-center mb-4">
            <svg class="w-10 h-10 text-primary-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Invoice Valid!</h1>
        <p class="text-gray-500 mb-6">Dokumen ini resmi diterbitkan oleh Sistem PMS Hotel Insitu Syariah Garut.</p>
        
        <div class="bg-surface-soft rounded-lg p-4 text-left space-y-3">
            <div>
                <div class="text-sm text-gray-500">No. Invoice</div>
                <div class="font-bold text-lg text-primary-dark">{{ $invoice->invoice_number }}</div>
            </div>
            <div>
                <div class="text-sm text-gray-500">Nama Tamu</div>
                <div class="font-semibold text-gray-800">{{ $invoice->reservation->guest_name }}</div>
            </div>
            <div class="flex justify-between items-center border-t border-gray-200 pt-3 mt-3">
                <div class="text-sm text-gray-500">Status Pembayaran</div>
                @if($invoice->payment_status == 'Paid')
                    <span class="bg-status-green text-white px-3 py-1 rounded-full text-sm font-bold shadow-sm">LUNAS</span>
                @else
                    <span class="bg-status-red text-white px-3 py-1 rounded-full text-sm font-bold shadow-sm">{{ strtoupper($invoice->payment_status) }}</span>
                @endif
            </div>
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-500">Total Transaksi</div>
                <div class="font-bold text-gray-800">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
            </div>
        </div>
        
        <div class="mt-8 text-xs text-gray-400">
            Diverifikasi pada: {{ now()->format('d M Y H:i:s') }}
        </div>
    </div>
</body>
</html>
