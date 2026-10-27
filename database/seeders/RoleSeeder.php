<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 3 Baseline Roles per PRD Section 4.1
        $managerRole = Role::firstOrCreate(['name' => 'Manager']);
        $receptionistRole = Role::firstOrCreate(['name' => 'Resepsionis']);
        $housekeepingRole = Role::firstOrCreate(['name' => 'Housekeeping']);

        // Default Seeded Users for testing each role
        $manager = User::firstOrCreate(
            ['email' => 'manager@insitu.id'],
            [
                'name' => 'Manager InSitu',
                'username' => 'manager',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $manager->syncRoles([$managerRole]);

        $receptionist = User::firstOrCreate(
            ['email' => 'resepsionis@insitu.id'],
            [
                'name' => 'Resepsionis Front Desk',
                'username' => 'resepsionis',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $receptionist->syncRoles([$receptionistRole]);

        $housekeeping = User::firstOrCreate(
            ['email' => 'housekeeping@insitu.id'],
            [
                'name' => 'Tim Housekeeping',
                'username' => 'housekeeping',
                'password' => Hash::make('password123'),
                'is_active' => true,
            ]
        );
        $housekeeping->syncRoles([$housekeepingRole]);

        // Seed granular permissions and default assignments
        $this->call(RolePermissionSeeder::class);
    }
}
