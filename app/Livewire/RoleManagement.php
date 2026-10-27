<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

#[Layout('layouts.app')]
class RoleManagement extends Component
{
    public ?int $selectedRoleId = null;
    public array $selectedPermissions = [];

    // Modals
    public bool $showCreateRoleModal = false;
    public bool $showEditRoleModal = false;
    public string $newRoleName = '';
    public string $editRoleName = '';

    // Core protected roles
    protected array $protectedRoles = ['Manager', 'Resepsionis', 'Housekeeping', 'Admin', 'admin'];

    public function mount(): void
    {
        $this->authorizeAccess();

        $firstRole = Role::where('name', 'Resepsionis')->first() ?? Role::first();
        if ($firstRole) {
            $this->selectRole($firstRole->id);
        }
    }

    private function authorizeAccess(): void
    {
        $user = Auth::user();
        if (!$user || (!$user->hasRole('Manager') && !$user->hasRole('Admin') && !$user->hasRole('admin'))) {
            abort(403, 'Akses terbatas untuk Manager/Admin.');
        }
    }

    public function selectRole(int $roleId): void
    {
        $role = Role::with('permissions')->findOrFail($roleId);
        $this->selectedRoleId = $role->id;

        if ($role->name === 'Manager') {
            $this->selectedPermissions = PermissionService::getAllPermissionCodes();
        } else {
            $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        }
    }

    public function toggleModulePermissions(string $moduleKey, bool $state): void
    {
        $modules = PermissionService::getPermissionsGrouped();
        if (!isset($modules[$moduleKey])) {
            return;
        }

        $moduleCodes = array_column($modules[$moduleKey]['permissions'], 'code');

        if ($state) {
            // Add all
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $moduleCodes));
        } else {
            // Remove all
            $this->selectedPermissions = array_values(array_diff($this->selectedPermissions, $moduleCodes));
        }
    }

    public function selectAllPermissions(): void
    {
        $role = Role::find($this->selectedRoleId);
        if ($role && $role->name !== 'Manager') {
            $this->selectedPermissions = PermissionService::getAllPermissionCodes();
        }
    }

    public function clearAllPermissions(): void
    {
        $role = Role::find($this->selectedRoleId);
        if ($role && $role->name !== 'Manager') {
            $this->selectedPermissions = [];
        }
    }

    public function savePermissions(): void
    {
        $this->authorizeAccess();

        $role = Role::findOrFail($this->selectedRoleId);

        if ($role->name === 'Manager') {
            session()->flash('info', 'Role Manager otomatis memiliki seluruh hak akses sistem sebagai Super Admin.');
            return;
        }

        $oldPermissions = $role->permissions->pluck('name')->toArray();
        $role->syncPermissions($this->selectedPermissions);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        AuditLog::record(
            action: 'role_permissions_updated',
            auditable: $role,
            oldValues: ['permissions' => $oldPermissions],
            newValues: ['permissions' => $this->selectedPermissions],
            reason: "Memperbarui matriks hak akses untuk role: {$role->name}",
            userId: Auth::id()
        );

        session()->flash('success', "Hak akses untuk role \"{$role->name}\" berhasil disimpan.");
    }

    public function openCreateRoleModal(): void
    {
        $this->resetValidation();
        $this->newRoleName = '';
        $this->showCreateRoleModal = true;
    }

    public function createRole(): void
    {
        $this->authorizeAccess();

        $validated = $this->validate([
            'newRoleName' => ['required', 'string', 'max:50', 'unique:roles,name'],
        ], [
            'newRoleName.required' => 'Nama role wajib diisi.',
            'newRoleName.unique' => 'Nama role ini sudah terdaftar.',
        ]);

        $role = Role::create([
            'name' => trim($validated['newRoleName']),
            'guard_name' => 'web',
        ]);

        // Default: give dashboard view
        $role->givePermissionTo('dashboard.view');

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        AuditLog::record(
            action: 'role_created',
            auditable: $role,
            oldValues: null,
            newValues: ['name' => $role->name],
            reason: "Menambahkan role baru: {$role->name}",
            userId: Auth::id()
        );

        $this->showCreateRoleModal = false;
        $this->selectRole($role->id);

        session()->flash('success', "Role baru \"{$role->name}\" berhasil ditambahkan. Silakan atur hak aksesnya.");
    }

    public function openEditRoleModal(int $roleId): void
    {
        $role = Role::findOrFail($roleId);

        if (in_array($role->name, $this->protectedRoles)) {
            session()->flash('error', "Role sistem bawaan \"{$role->name}\" dilindungi dan tidak dapat diubah namanya.");
            return;
        }

        $this->resetValidation();
        $this->selectedRoleId = $role->id;
        $this->editRoleName = $role->name;
        $this->showEditRoleModal = true;
    }

    public function updateRole(): void
    {
        $this->authorizeAccess();

        $role = Role::findOrFail($this->selectedRoleId);

        if (in_array($role->name, $this->protectedRoles)) {
            session()->flash('error', "Role sistem bawaan tidak dapat diubah namanya.");
            return;
        }

        $validated = $this->validate([
            'editRoleName' => ['required', 'string', 'max:50', 'unique:roles,name,' . $role->id],
        ], [
            'editRoleName.required' => 'Nama role wajib diisi.',
            'editRoleName.unique' => 'Nama role ini sudah digunakan.',
        ]);

        $oldName = $role->name;
        $role->name = trim($validated['editRoleName']);
        $role->save();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        AuditLog::record(
            action: 'role_updated',
            auditable: $role,
            oldValues: ['name' => $oldName],
            newValues: ['name' => $role->name],
            reason: "Mengubah nama role dari \"{$oldName}\" menjadi \"{$role->name}\"",
            userId: Auth::id()
        );

        $this->showEditRoleModal = false;
        session()->flash('success', "Nama role berhasil diperbarui menjadi \"{$role->name}\".");
    }

    public function deleteRole(int $roleId): void
    {
        $this->authorizeAccess();

        $role = Role::findOrFail($roleId);

        if (in_array($role->name, $this->protectedRoles)) {
            session()->flash('error', "Role sistem bawaan \"{$role->name}\" dilindungi dan tidak dapat dihapus.");
            return;
        }

        $assignedCount = User::role($role->name)->count();
        if ($assignedCount > 0) {
            session()->flash('error', "Role \"{$role->name}\" masih digunakan oleh {$assignedCount} staf aktif. Pindahkan staf ke role lain terlebih dahulu sebelum menghapus role ini.");
            return;
        }

        $roleName = $role->name;

        AuditLog::record(
            action: 'role_deleted',
            auditable: null,
            oldValues: ['name' => $role->name],
            newValues: null,
            reason: "Menghapus role kustom: {$roleName}",
            userId: Auth::id()
        );

        $role->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $fallbackRole = Role::first();
        if ($fallbackRole) {
            $this->selectRole($fallbackRole->id);
        }

        session()->flash('success', "Role \"{$roleName}\" berhasil dihapus.");
    }

    public function render()
    {
        $roles = Role::withCount('users')->orderBy('id')->get();
        $selectedRole = Role::with('permissions')->find($this->selectedRoleId);
        $groupedModules = PermissionService::getPermissionsGrouped();

        return view('livewire.role-management', [
            'roles' => $roles,
            'selectedRole' => $selectedRole,
            'groupedModules' => $groupedModules,
            'protectedRoles' => $this->protectedRoles,
        ]);
    }
}
