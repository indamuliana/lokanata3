<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
class UserManagement extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = 'all'; // all, Manager, Resepsionis, Housekeeping
    public string $statusFilter = 'all'; // all, active, inactive

    // Modals
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public bool $showResetPasswordModal = false;

    // Form inputs
    public ?int $selectedUserId = null;
    public string $name = '';
    public string $username = '';
    public string $email = '';
    public string $phone = '';
    public string $role = 'Resepsionis';
    public bool $is_active = true;

    // Password inputs
    public string $password = '';
    public string $password_confirmation = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';

    public function mount(): void
    {
        $this->authorizeAccess();
    }

    private function authorizeAccess(): void
    {
        $user = Auth::user();
        if (!$user || (!$user->hasRole('Manager') && !$user->hasRole('Admin') && !$user->hasRole('admin'))) {
            abort(403, 'Akses terbatas untuk Manager/Admin.');
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetValidation();
        $this->reset(['selectedUserId', 'name', 'username', 'email', 'phone', 'password', 'password_confirmation']);
        $this->role = 'Resepsionis';
        $this->is_active = true;
        $this->showCreateModal = true;
    }

    public function createUser(): void
    {
        $this->authorizeAccess();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
            'is_active' => ['boolean'],
        ], [
            'username.unique' => 'Username ini sudah digunakan oleh pengguna lain.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.min' => 'Password minimal harus 8 karakter.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => $this->is_active,
        ]);

        $user->syncRoles([$validated['role']]);

        AuditLog::record(
            action: 'user_created',
            auditable: $user,
            oldValues: null,
            newValues: [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $validated['role'],
                'is_active' => $user->is_active,
            ],
            reason: "Menambahkan staf baru: {$user->name} ({$validated['role']})",
            userId: Auth::id()
        );

        $this->showCreateModal = false;
        session()->flash('success', "Pengguna {$user->name} berhasil ditambahkan dengan role {$validated['role']}.");
    }

    public function openEditModal(int $id): void
    {
        $this->resetValidation();
        $user = User::findOrFail($id);
        $this->selectedUserId = $user->id;
        $this->name = $user->name;
        $this->username = $user->username ?? '';
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->role = $user->roles->first()?->name ?? 'Resepsionis';
        $this->is_active = (bool) $user->is_active;

        $this->showEditModal = true;
    }

    public function updateUser(): void
    {
        $this->authorizeAccess();

        $user = User::findOrFail($this->selectedUserId);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'is_active' => ['boolean'],
        ]);

        // Safety: cannot demote last manager
        if ($user->hasRole('Manager') && $validated['role'] !== 'Manager') {
            $managerCount = User::role('Manager')->where('is_active', true)->count();
            if ($managerCount <= 1) {
                $this->addError('role', 'Tidak dapat mengubah role Manager terakhir dalam sistem.');
                return;
            }
        }

        // Safety: cannot deactivate self
        if ($user->id === Auth::id() && !$this->is_active) {
            $this->addError('is_active', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
            return;
        }

        $old = [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role_name,
            'is_active' => $user->is_active,
        ];

        $user->update([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'] ?? null,
            'is_active' => $this->is_active,
        ]);

        $user->syncRoles([$validated['role']]);

        AuditLog::record(
            action: 'user_updated',
            auditable: $user,
            oldValues: $old,
            newValues: [
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $validated['role'],
                'is_active' => $user->is_active,
            ],
            reason: "Memperbarui profil staf: {$user->name}",
            userId: Auth::id()
        );

        $this->showEditModal = false;
        session()->flash('success', "Data profil pengguna {$user->name} berhasil diperbarui.");
    }

    public function openResetPasswordModal(int $id): void
    {
        $this->resetValidation();
        $user = User::findOrFail($id);
        $this->selectedUserId = $user->id;
        $this->name = $user->name;
        $this->reset(['new_password', 'new_password_confirmation']);
        $this->showResetPasswordModal = true;
    }

    public function resetPassword(): void
    {
        $this->authorizeAccess();

        $user = User::findOrFail($this->selectedUserId);

        $validated = $this->validate([
            'new_password' => ['required', 'string', Password::min(8), 'confirmed'],
        ], [
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'new_password.min' => 'Password baru minimal harus 8 karakter.',
        ]);

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        AuditLog::record(
            action: 'user_password_reset',
            auditable: $user,
            oldValues: null,
            newValues: ['password_changed' => true],
            reason: "Reset password untuk staf: {$user->name} oleh " . Auth::user()->name,
            userId: Auth::id()
        );

        $this->showResetPasswordModal = false;
        session()->flash('success', "Password untuk {$user->name} berhasil diubah.");
    }

    public function toggleStatus(int $id): void
    {
        $this->authorizeAccess();

        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            session()->flash('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
            return;
        }

        if ($user->hasRole('Manager') && $user->is_active) {
            $managerCount = User::role('Manager')->where('is_active', true)->count();
            if ($managerCount <= 1) {
                session()->flash('error', 'Tidak dapat menonaktifkan Manager aktif terakhir dalam sistem.');
                return;
            }
        }

        $newStatus = !$user->is_active;
        $user->is_active = $newStatus;
        $user->save();

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        AuditLog::record(
            action: 'user_status_toggled',
            auditable: $user,
            oldValues: ['is_active' => !$newStatus],
            newValues: ['is_active' => $newStatus],
            reason: "Akun staf {$user->name} {$statusText}",
            userId: Auth::id()
        );

        session()->flash('success', "Akun {$user->name} berhasil {$statusText}.");
    }

    public function deleteUser(int $id): void
    {
        $this->authorizeAccess();

        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            session()->flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            return;
        }

        if ($user->hasRole('Manager')) {
            $managerCount = User::role('Manager')->count();
            if ($managerCount <= 1) {
                session()->flash('error', 'Tidak dapat menghapus akun Manager terakhir.');
                return;
            }
        }

        if ($user->hasHistoricalData()) {
            session()->flash('error', "Pengguna {$user->name} memiliki riwayat transaksi/audit dalam sistem sehingga tidak dapat dihapus. Anda dapat menonaktifkan status akunnya.");
            return;
        }

        $userName = $user->name;

        AuditLog::record(
            action: 'user_deleted',
            auditable: null,
            oldValues: [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role_name,
            ],
            newValues: null,
            reason: "Menghapus akun staf: {$userName}",
            userId: Auth::id()
        );

        $user->delete();

        session()->flash('success', "Akun pengguna {$userName} berhasil dihapus.");
    }

    public function render()
    {
        $query = User::with('roles')->latest();

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('username', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%");
            });
        }

        if ($this->roleFilter !== 'all') {
            $query->role($this->roleFilter);
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        $users = $query->paginate(10);
        $roles = Role::orderBy('name')->get();

        // Summary counts
        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $managerCount = User::role('Manager')->count();
        $receptionistCount = User::role('Resepsionis')->count();
        $housekeepingCount = User::role('Housekeeping')->count();

        return view('livewire.user-management', [
            'users' => $users,
            'roles' => $roles,
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'managerCount' => $managerCount,
            'receptionistCount' => $receptionistCount,
            'housekeepingCount' => $housekeepingCount,
        ]);
    }
}
