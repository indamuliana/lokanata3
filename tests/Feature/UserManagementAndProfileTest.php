<?php

namespace Tests\Feature;

use App\Livewire\Forms\LoginForm;
use App\Livewire\UserManagement;
use App\Models\AuditLog;
use App\Models\Reservation;
use App\Models\RoomType;
use App\Models\User;
use App\Services\ReservationService;
use Database\Seeders\DefaultSettingsSeeder;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementAndProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $receptionist;

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
    }

    public function test_user_management_route_access_rbac(): void
    {
        // 1. Guest redirected to login
        $this->get('/users')->assertRedirect('/login');

        // 2. Receptionist receives 403
        $this->actingAs($this->receptionist)->get('/users')->assertStatus(403);

        // 3. Manager receives 200 OK
        $this->actingAs($this->manager)->get('/users')->assertStatus(200);
    }

    public function test_manager_can_create_new_staff_user(): void
    {
        Livewire::actingAs($this->manager)
            ->test(UserManagement::class)
            ->set('name', 'Budi Santoso')
            ->set('username', 'budisantoso')
            ->set('email', 'budi@insitu.id')
            ->set('phone', '081234567899')
            ->set('role', 'Resepsionis')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('is_active', true)
            ->call('createUser')
            ->assertHasNoErrors();

        $newUser = User::where('email', 'budi@insitu.id')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('budisantoso', $newUser->username);
        $this->assertTrue($newUser->hasRole('Resepsionis'));
        $this->assertTrue($newUser->is_active);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'user_created',
            'user_id' => $this->manager->id,
        ]);
    }

    public function test_manager_can_update_user_and_change_role(): void
    {
        $staff = User::factory()->create([
            'username' => 'stafflama',
            'is_active' => true,
        ]);
        $staff->assignRole('Resepsionis');

        Livewire::actingAs($this->manager)
            ->test(UserManagement::class)
            ->call('openEditModal', $staff->id)
            ->set('name', 'Staff Baru Diperbarui')
            ->set('username', 'staffbaru')
            ->set('email', 'staffbaru@insitu.id')
            ->set('phone', '089988776655')
            ->set('role', 'Housekeeping')
            ->set('is_active', true)
            ->call('updateUser')
            ->assertHasNoErrors();

        $staff->refresh();
        $this->assertEquals('Staff Baru Diperbarui', $staff->name);
        $this->assertEquals('staffbaru', $staff->username);
        $this->assertTrue($staff->hasRole('Housekeeping'));
    }

    public function test_manager_can_reset_staff_password(): void
    {
        $staff = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);
        $staff->assignRole('Resepsionis');

        Livewire::actingAs($this->manager)
            ->test(UserManagement::class)
            ->call('openResetPasswordModal', $staff->id)
            ->set('new_password', 'newsecretpass123')
            ->set('new_password_confirmation', 'newsecretpass123')
            ->call('resetPassword')
            ->assertHasNoErrors();

        $staff->refresh();
        $this->assertTrue(Hash::check('newsecretpass123', $staff->password));
    }

    public function test_toggle_status_and_inactive_user_cannot_login(): void
    {
        $staff = User::factory()->create([
            'email' => 'active_staff@insitu.id',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
        $staff->assignRole('Resepsionis');

        // 1. Deactivate user via Livewire
        Livewire::actingAs($this->manager)
            ->test(UserManagement::class)
            ->call('toggleStatus', $staff->id);

        $staff->refresh();
        $this->assertFalse($staff->is_active);

        // 2. Try logging in as deactivated user
        auth()->logout();
        Livewire::test('pages.auth.login')
            ->set('form.email', 'active_staff@insitu.id')
            ->set('form.password', 'password123')
            ->call('login')
            ->assertHasErrors(['form.email']);

        $this->assertGuest();

        // 3. Reactivate user via Livewire
        Livewire::actingAs($this->manager)
            ->test(UserManagement::class)
            ->call('toggleStatus', $staff->id);

        $staff->refresh();
        $this->assertTrue($staff->is_active);

        // 4. Now can login
        auth()->logout();
        Livewire::test('pages.auth.login')
            ->set('form.email', 'active_staff@insitu.id')
            ->set('form.password', 'password123')
            ->call('login')
            ->assertHasNoErrors();

        $this->assertAuthenticatedAs($staff);
    }

    public function test_safety_cannot_delete_self_or_user_with_history(): void
    {
        // 1. Manager cannot delete self
        Livewire::actingAs($this->manager)
            ->test(UserManagement::class)
            ->call('deleteUser', $this->manager->id);

        $this->assertDatabaseHas('users', ['id' => $this->manager->id]);

        // 2. Staff with reservation history cannot be deleted
        $stdType = RoomType::where('code', 'STD')->first();
        $staffWithHistory = User::factory()->create();
        $staffWithHistory->assignRole('Resepsionis');

        ReservationService::createReservation([
            'room_type_id' => $stdType->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'source' => 'walk_in',
            'customer_name' => 'Tamu Staff',
            'customer_phone' => '081234567800',
            'initial_paid_amount' => 0,
        ]);
        // Set created_by to this staff
        Reservation::latest()->first()->update(['created_by' => $staffWithHistory->id]);

        Livewire::actingAs($this->manager)
            ->test(UserManagement::class)
            ->call('deleteUser', $staffWithHistory->id);

        // Staff remains in database
        $this->assertDatabaseHas('users', ['id' => $staffWithHistory->id]);
    }

    public function test_user_can_update_profile_and_password_in_profile_page(): void
    {
        // 1. Test update profile information
        Livewire::actingAs($this->receptionist)
            ->test('profile.update-profile-information-form')
            ->set('name', 'Resepsionis Updated')
            ->set('username', 'resepsionis_baru')
            ->set('email', 'resepsionis_baru@insitu.id')
            ->set('phone', '081122334455')
            ->call('updateProfileInformation')
            ->assertHasNoErrors();

        $this->receptionist->refresh();
        $this->assertEquals('Resepsionis Updated', $this->receptionist->name);
        $this->assertEquals('resepsionis_baru', $this->receptionist->username);
        $this->assertEquals('081122334455', $this->receptionist->phone);

        // 2. Test update password with correct current password
        Livewire::actingAs($this->receptionist)
            ->test('profile.update-password-form')
            ->set('current_password', 'password123')
            ->set('password', 'newPassword456!')
            ->set('password_confirmation', 'newPassword456!')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->receptionist->refresh();
        $this->assertTrue(Hash::check('newPassword456!', $this->receptionist->password));

        // 3. Test update password fails with incorrect current password
        Livewire::actingAs($this->receptionist)
            ->test('profile.update-password-form')
            ->set('current_password', 'wrongPassword')
            ->set('password', 'anotherNewPassword789!')
            ->set('password_confirmation', 'anotherNewPassword789!')
            ->call('updatePassword')
            ->assertHasErrors(['current_password']);
    }
}
