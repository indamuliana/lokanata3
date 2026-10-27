<?php

namespace Tests\Feature;

use App\Livewire\ReservationManagement;
use App\Models\AdditionalService;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\RoomType;
use App\Models\User;
use App\Services\BillingService;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BillingAndReceiptTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;
    protected RoomType $roomType;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\DefaultSettingsSeeder::class);
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
        $this->seed(\Database\Seeders\AdditionalServiceSeeder::class);

        $this->staff = User::where('username', 'resepsionis')->first() ?? User::factory()->create(['username' => 'staff1']);
        $this->actingAs($this->staff);

        $this->roomType = RoomType::first();
        $this->customer = Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '6281234567890',
            'identity_type' => 'KTP',
            'identity_number' => '3205012345670001',
        ]);
    }

    public function test_can_record_multi_stage_payment_and_generate_invoice(): void
    {
        $res = ReservationService::createReservation([
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'source' => 'walk_in',
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
        ]);

        $initialTotal = $res->total_amount;
        $this->assertEquals(0, $res->paid_amount);
        $this->assertEquals('unpaid', $res->payment_status);

        // 1. Bayar DP 50%
        $dpAmount = (int) round($initialTotal / 2);
        $payment1 = BillingService::recordPayment($res, $dpAmount, 'bank_transfer', 'BCA', 'TRF-001');

        $res->refresh();
        $this->assertEquals($dpAmount, $res->paid_amount);
        $this->assertEquals('partially_paid', $res->payment_status);
        $this->assertDatabaseHas('payments', [
            'id' => $payment1->id,
            'amount' => $dpAmount,
            'payment_method' => 'bank_transfer',
            'bank_account' => 'BCA',
        ]);

        // Invoice harus dibuat otomatis
        $invoice = Invoice::where('reservation_id', $res->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals($dpAmount, $invoice->paid_amount);
        $this->assertEquals('partially_paid', $invoice->status);

        // 2. Pelunasan sisa tagihan
        $remaining = $res->total_amount - $res->paid_amount;
        $payment2 = BillingService::recordPayment($res, $remaining, 'cash');

        $res->refresh();
        $invoice->refresh();

        $this->assertEquals($initialTotal, $res->paid_amount);
        $this->assertEquals('paid', $res->payment_status);
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(0, $invoice->balance);
    }

    public function test_can_add_addon_service_and_recalculate_totals(): void
    {
        $res = ReservationService::createReservation([
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'source' => 'walk_in',
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
        ]);

        $initialSubtotal = $res->subtotal;
        $initialTotal = $res->total_amount;

        // Tambah Extra Bed 1x Rp 150.000
        $extraBed = AdditionalService::where('name', 'Extra Bed')->first();
        $item = BillingService::addServiceItem($res, 'Extra Bed', 1, 150000, $extraBed->id);

        $res->refresh();

        $expectedSubtotal = $initialSubtotal + 150000;
        $expectedTax = (int) round($expectedSubtotal * 0.10);
        $expectedTotal = $expectedSubtotal + $expectedTax;

        $this->assertEquals($expectedSubtotal, $res->subtotal);
        $this->assertEquals($expectedTax, $res->tax_amount);
        $this->assertEquals($expectedTotal, $res->total_amount);

        $this->assertDatabaseHas('reservation_service_items', [
            'id' => $item->id,
            'item_name' => 'Extra Bed',
            'quantity' => 1,
            'unit_price' => 150000,
            'subtotal' => 150000,
        ]);
    }

    public function test_can_process_refund_and_audit(): void
    {
        $res = ReservationService::createReservation([
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'source' => 'walk_in',
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'initial_paid_amount' => 300000,
        ]);

        $this->assertEquals(300000, $res->paid_amount);

        // Refund Rp 100.000
        $refund = BillingService::recordRefund($res, 100000, 'Tamu mengurangi pesanan');

        $res->refresh();
        $this->assertEquals(200000, $res->paid_amount);
        $this->assertDatabaseHas('payments', [
            'id' => $refund->id,
            'amount' => 100000,
            'is_refund' => true,
            'refund_reason' => 'Tamu mengurangi pesanan',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'payment_refund',
        ]);
    }

    public function test_thermal_receipt_renders_correctly(): void
    {
        $res = ReservationService::createReservation([
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'source' => 'walk_in',
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'initial_paid_amount' => 100000,
        ]);

        $response = $this->get(route('invoice.thermal', $res->id));
        $response->assertStatus(200);
        $response->assertSee('INSITU HOTEL SYARIAH GARUT');
        $response->assertSee('Struk Kasir');
        $response->assertSee($res->reservation_number);
        $response->assertSee('Scan QR untuk Verifikasi Keaslian');
    }

    public function test_invoice_pdf_streams_correctly(): void
    {
        $res = ReservationService::createReservation([
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'source' => 'walk_in',
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
        ]);

        $response = $this->get(route('invoice.pdf', $res->id));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_public_qr_verification_shows_details_without_pii(): void
    {
        $res = ReservationService::createReservation([
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'source' => 'walk_in',
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
            'customer_identity_number' => '3205012345670001',
        ]);

        $invoice = BillingService::generateInvoice($res);
        $token = $invoice->getOrCreateQrToken();

        // Akses publik tanpa auth
        $this->app['auth']->logout();

        $response = $this->get(route('document.verify', ['token' => $token]));
        $response->assertStatus(200);
        $response->assertSee('InSitu Hotel Syariah Garut');
        $response->assertSee('Dokumen Terverifikasi Sah dan Asli');
        $response->assertSee($invoice->invoice_number);
        $response->assertSee($res->roomType->name);

        // Strict PRD 17.2: Tamu PII (NIK & No. Telp) TIDAK BOLEH muncul di publik
        $response->assertDontSee('3205012345670001');
        $response->assertDontSee('6281234567890');
    }

    public function test_livewire_can_record_payment_and_add_addon(): void
    {
        $res = ReservationService::createReservation([
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'source' => 'walk_in',
            'customer_name' => $this->customer->name,
            'customer_phone' => $this->customer->phone,
        ]);

        Livewire::test(ReservationManagement::class)
            ->call('openPaymentModal', $res->id)
            ->set('payment_amount', 250000)
            ->set('payment_method', 'cash')
            ->call('savePayment')
            ->assertHasNoErrors();

        $res->refresh();
        $this->assertEquals(250000, $res->paid_amount);

        // Test tambah addon via Livewire
        Livewire::test(ReservationManagement::class)
            ->call('openAddonModal', $res->id)
            ->set('addon_item_name', 'Sarapan Tambahan')
            ->set('addon_quantity', 2)
            ->set('addon_unit_price', 50000)
            ->call('saveAddon')
            ->assertHasNoErrors();

        $res->refresh();
        $this->assertDatabaseHas('reservation_service_items', [
            'reservation_id' => $res->id,
            'item_name' => 'Sarapan Tambahan',
            'quantity' => 2,
            'subtotal' => 100000,
        ]);
    }
}
