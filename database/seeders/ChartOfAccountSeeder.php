<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // Aktiva / Asset
            ['code' => '1110', 'name' => 'Kas Front Desk', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1120', 'name' => 'Kas di Bank Operasional (BSI / BCA)', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1130', 'name' => 'Piutang Tamu (Guest Ledger)', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1140', 'name' => 'Piutang Portal OTA', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1150', 'name' => 'Persediaan Barang (Amenities, Linen, F&B)', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1210', 'name' => 'Aset Tetap Peralatan & Furnitur', 'type' => 'asset', 'normal_balance' => 'debit'],
            ['code' => '1220', 'name' => 'Akumulasi Penyusutan Aset Tetap', 'type' => 'asset', 'normal_balance' => 'credit'],

            // Kewajiban / Liability
            ['code' => '2110', 'name' => 'Uang Muka Tamu / Deposit Reservasi', 'type' => 'liability', 'normal_balance' => 'credit'],
            ['code' => '2120', 'name' => 'Utang Pajak Daerah PB1 (10%)', 'type' => 'liability', 'normal_balance' => 'credit'],

            // Ekuitas / Equity
            ['code' => '3100', 'name' => 'Modal Pemilik', 'type' => 'equity', 'normal_balance' => 'credit'],
            ['code' => '3200', 'name' => 'Laba Ditahan', 'type' => 'equity', 'normal_balance' => 'credit'],

            // Pendapatan / Revenue
            ['code' => '4110', 'name' => 'Pendapatan Sewa Kamar', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '4120', 'name' => 'Pendapatan Ruang Meeting', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '4130', 'name' => 'Pendapatan Restoran Syariah', 'type' => 'revenue', 'normal_balance' => 'credit'],
            ['code' => '4140', 'name' => 'Pendapatan Layanan Tambahan (Add-on)', 'type' => 'revenue', 'normal_balance' => 'credit'],

            // Beban / Expense
            ['code' => '5110', 'name' => 'Beban Perlengkapan & Amenities', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5120', 'name' => 'Beban Utilitas (Listrik, Air, Wifi)', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5130', 'name' => 'Beban Pemeliharaan & Perbaikan Kamar', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5140', 'name' => 'Beban Komisi Portal OTA', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5150', 'name' => 'Beban Dapur & Konsumsi Resto', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5160', 'name' => 'Beban Penyusutan Aset Tetap', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5170', 'name' => 'Beban Penyesuaian & Selisih Persediaan', 'type' => 'expense', 'normal_balance' => 'debit'],
            ['code' => '5190', 'name' => 'Beban Operasional Lain-lain', 'type' => 'expense', 'normal_balance' => 'debit'],
        ];

        foreach ($accounts as $acc) {
            ChartOfAccount::updateOrCreate(
                ['code' => $acc['code']],
                $acc
            );
        }
    }
}
