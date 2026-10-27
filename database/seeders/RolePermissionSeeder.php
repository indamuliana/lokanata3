<?php

namespace Database\Seeders;

use App\Services\PermissionService;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create or update all permissions
        $allPermissions = PermissionService::getAllPermissionCodes();
        foreach ($allPermissions as $code) {
            Permission::firstOrCreate(['name' => $code, 'guard_name' => 'web']);
        }

        // 2. Roles
        $managerRole = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $receptionistRole = Role::firstOrCreate(['name' => 'Resepsionis', 'guard_name' => 'web']);
        $housekeepingRole = Role::firstOrCreate(['name' => 'Housekeeping', 'guard_name' => 'web']);

        // 3. Manager: Give all permissions
        $managerRole->syncPermissions($allPermissions);

        // 4. Resepsionis: Front Desk & Operational
        $receptionistPermissions = [
            'dashboard.view',
            'room_status.edit',
            'room_plot.edit',
            'reservations.view',
            'reservations.manage',
            'checkin_checkout.manage',
            'reservation_payment.manage',
            'meeting.view',
            'meeting.manage',
            'dining.view',
            'dining.manage',
            'calendar.view',
            'cashier.view',
            'cashier.manage',
            'expenses.view',
            'expenses.create',
            'inventory.view',
            'inventory.manage',
            'rooms_master.view',
        ];
        $receptionistRole->syncPermissions($receptionistPermissions);

        // 5. Housekeeping: Room cleaning, maintenance & read-only reservation/inventory
        $housekeepingPermissions = [
            'dashboard.view',
            'room_status.edit',
            'maintenance.manage',
            'reservations.view',
            'inventory.view',
        ];
        $housekeepingRole->syncPermissions($housekeepingPermissions);
    }
}
