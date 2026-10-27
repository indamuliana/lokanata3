<?php

namespace Tests\Feature;

use App\Livewire\ReservationManagement;
use App\Livewire\RoleManagement;
use App\Livewire\RoomControlDashboard;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\ReservationRoom;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Services\PermissionService;
use Database\Seeders\DefaultSettingsSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleAndPermissionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $receptionist;
    protected User $housekeeping;
    protected Room $testRoom;
    protected Reservation $testReservation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([
            RoleSeeder::class,
            DefaultSettingsSeeder::class,
            MasterDataSeeder::class,
        ]);

        $this->manager = User::where('email', 'manager@insitu.id')->first();
        $this->receptionist = User::where('email', 'resepsionis@insitu.id')->first();
        $this->housekeeping = User::where('email', 'housekeeping@insitu.id')->first();

        $this->testRoom = Room::first();
        $roomType = RoomType::first();
        $customer = Customer::create([
            'name' => 'Tamu Uji Hak Akses',
            'phone' => '081299998888',
            'email' => 'tamu.rbac@test.com',
            'identity_type' => 'KTP',
            'identity_number' => '3201123456789001',
        ]);

        $this->testReservation = Reservation::create([
            'reservation_number' => 'RES-TEST-RBAC',
            'customer_id' => $customer->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'status' => 'confirmed',
            'source' => 'walk_in',
            'adults' => 2,
            'subtotal' => 500000,
            'total_amount' => 500000,
            'paid_amount' => 500000,
            'payment_status' => 'paid',
        ]);

        ReservationRoom::create([
            'reservation_id' => $this->testReservation->id,
            'room_id' => $this->testRoom->id,
            'assigned_from' => $this->testReservation->check_in_date,
            'assigned_to' => $this->testReservation->check_out_date,
        ]);
    }

    public function test_roles_management_route_access_rbac(): void
    {
        // 1. Guest redirected to login
        $this->get('/roles')->assertRedirect('/login');

        // 2. Housekeeping receives 403 Forbidden
        $this->actingAs($this->housekeeping)->get('/roles')->assertStatus(403);

        // 3. Receptionist receives 403 Forbidden
        $this->actingAs($this->receptionist)->get('/roles')->assertStatus(403);

        // 4. Manager receives 200 OK
        $this->actingAs($this->manager)->get('/roles')->assertStatus(200);
    }

    public function test_manager_can_create_new_custom_role_and_assign_permissions(): void
    {
        Livewire::actingAs($this->manager)
            ->test(RoleManagement::class)
            ->set('newRoleName', 'Floor Supervisor')
            ->call('createRole')
            ->assertHasNoErrors()
            ->assertSee('Floor Supervisor');

        $role = Role::where('name', 'Floor Supervisor')->first();
        $this->assertNotNull($role);

        // Assign selected permissions
        $permissions = ['dashboard.view', 'room_status.edit', 'room_plot.edit', 'maintenance.manage'];
        Livewire::actingAs($this->manager)
            ->test(RoleManagement::class)
            ->call('selectRole', $role->id)
            ->set('selectedPermissions', $permissions)
            ->call('savePermissions')
            ->assertHasNoErrors();

        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('room_status.edit'));
        $this->assertTrue($role->hasPermissionTo('room_plot.edit'));
        $this->assertFalse($role->hasPermissionTo('checkin_checkout.manage'));
    }

    public function test_manager_can_select_all_and_clear_all_permissions_in_role_management(): void
    {
        $role = Role::where('name', 'Resepsionis')->first();

        // 1. Select all permissions
        Livewire::actingAs($this->manager)
            ->test(RoleManagement::class)
            ->call('selectRole', $role->id)
            ->call('selectAllPermissions')
            ->assertSet('selectedPermissions', PermissionService::getAllPermissionCodes());

        // 2. Clear all permissions
        Livewire::actingAs($this->manager)
            ->test(RoleManagement::class)
            ->call('selectRole', $role->id)
            ->call('clearAllPermissions')
            ->assertSet('selectedPermissions', []);
    }

    public function test_core_roles_protected_from_deletion(): void
    {
        $managerRole = Role::where('name', 'Manager')->first();
        $housekeepingRole = Role::where('name', 'Housekeeping')->first();

        Livewire::actingAs($this->manager)
            ->test(RoleManagement::class)
            ->call('deleteRole', $managerRole->id)
            ->assertSee('dilindungi dan tidak dapat dihapus');

        $this->assertDatabaseHas('roles', ['name' => 'Manager']);

        Livewire::actingAs($this->manager)
            ->test(RoleManagement::class)
            ->call('deleteRole', $housekeepingRole->id)
            ->assertSee('dilindungi dan tidak dapat dihapus');

        $this->assertDatabaseHas('roles', ['name' => 'Housekeeping']);
    }

    public function test_custom_role_deletion_rules(): void
    {
        // 1. Create custom role
        $role = Role::create(['name' => 'Staff Magang', 'guard_name' => 'web']);

        // 2. Assign to a user
        $user = User::factory()->create(['username' => 'magang1']);
        $user->assignRole($role);

        // 3. Try to delete while in use -> error
        Livewire::actingAs($this->manager)
            ->test(RoleManagement::class)
            ->call('deleteRole', $role->id)
            ->assertSee('masih digunakan oleh 1 staf aktif');

        $this->assertDatabaseHas('roles', ['name' => 'Staff Magang']);

        // 4. Remove user assignment and delete successfully
        $user->removeRole($role);

        Livewire::actingAs($this->manager)
            ->test(RoleManagement::class)
            ->call('deleteRole', $role->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('roles', ['name' => 'Staff Magang']);
    }

    public function test_housekeeping_can_update_cleaning_status(): void
    {
        $this->testRoom->update(['cleaning_status' => 'dirty']);

        Livewire::actingAs($this->housekeeping)
            ->test(RoomControlDashboard::class)
            ->call('openCleaningModal', $this->testRoom->id)
            ->set('new_cleaning_status', 'clean')
            ->call('saveCleaning')
            ->assertHasNoErrors();

        $this->assertEquals('clean', $this->testRoom->fresh()->cleaning_status);
    }

    public function test_housekeeping_cannot_plot_room(): void
    {
        Livewire::actingAs($this->housekeeping)
            ->test(RoomControlDashboard::class)
            ->call('openPlotModal', $this->testReservation->id)
            ->assertForbidden();
    }

    public function test_housekeeping_cannot_checkin_guest_from_dashboard(): void
    {
        Livewire::actingAs($this->housekeeping)
            ->test(RoomControlDashboard::class)
            ->call('openCheckIn', $this->testReservation->id, $this->testRoom->id)
            ->assertForbidden();
    }

    public function test_housekeeping_cannot_checkin_guest_from_reservation_management(): void
    {
        Livewire::actingAs($this->housekeeping)
            ->test(ReservationManagement::class)
            ->call('checkIn', $this->testReservation->id)
            ->assertForbidden();
    }

    public function test_housekeeping_cannot_checkout_guest_from_dashboard(): void
    {
        Livewire::actingAs($this->housekeeping)
            ->test(RoomControlDashboard::class)
            ->call('openCheckOut', $this->testReservation->id)
            ->assertForbidden();
    }

    public function test_housekeeping_cannot_create_reservations(): void
    {
        Livewire::actingAs($this->housekeeping)
            ->test(ReservationManagement::class)
            ->call('openCreateModal')
            ->assertForbidden();
    }

    public function test_manager_super_admin_bypass_allows_all_actions(): void
    {
        $this->testRoom->update(['cleaning_status' => 'dirty', 'operational_status' => 'active']);

        // 1. Manager can update cleaning status
        Livewire::actingAs($this->manager)
            ->test(RoomControlDashboard::class)
            ->call('openCleaningModal', $this->testRoom->id)
            ->set('new_cleaning_status', 'inspected')
            ->call('saveCleaning')
            ->assertHasNoErrors();

        $this->assertEquals('inspected', $this->testRoom->fresh()->cleaning_status);

        // 2. Manager can check-in
        Livewire::actingAs($this->manager)
            ->test(RoomControlDashboard::class)
            ->call('openCheckIn', $this->testReservation->id, $this->testRoom->id)
            ->assertHasNoErrors();

        $this->assertEquals('checked_in', $this->testReservation->fresh()->status);

        // 3. Manager can check-out
        Livewire::actingAs($this->manager)
            ->test(RoomControlDashboard::class)
            ->call('openCheckOut', $this->testReservation->id)
            ->assertHasNoErrors();

        $this->assertEquals('checked_out', $this->testReservation->fresh()->status);
    }

    public function test_navigation_menu_respects_permissions(): void
    {
        // Manager sees settings and roles
        $responseManager = $this->actingAs($this->manager)->get('/dashboard');
        $responseManager->assertStatus(200);
        $responseManager->assertSee('Role & Hak Akses');
        $responseManager->assertSee('Manajemen User');

        // Housekeeping does NOT see settings, roles, or users in navigation
        $responseHk = $this->actingAs($this->housekeeping)->get('/dashboard');
        $responseHk->assertStatus(200);
        $responseHk->assertDontSee('Role & Hak Akses');
        $responseHk->assertDontSee('Manajemen User');
        $responseHk->assertSee('Kendali Kamar');
    }
}
