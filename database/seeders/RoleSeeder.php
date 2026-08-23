<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // ADMIN ROLE
        // =========================
        $admin = Role::firstOrCreate(['name' => 'admin']);

        // =========================
        // DOCTOR ROLE
        // =========================
        $doctor = Role::firstOrCreate(['name' => 'doctor']);

        // =========================
        // ASSISTANT ROLE
        // =========================
        $assistant = Role::firstOrCreate(['name' => 'assistant']);

        // =========================
        // MODULE PERMISSIONS
        // =========================
        $modulePermissions = [
            'smart-serial-manage',
        ];

        foreach ($modulePermissions as $permissionName) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);

            $doctor->givePermissionTo($permission);
            $assistant->givePermissionTo($permission);
        }
    }
}