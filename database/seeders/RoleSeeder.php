<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // =========================
        // ADMIN ROLE
        // =========================
        Role::firstOrCreate([
            'name' => 'admin'
        ]);

        // =========================
        // DOCTOR ROLE
        // =========================
        Role::firstOrCreate([
            'name' => 'doctor'
        ]);

        // =========================
        // ASSISTANT ROLE
        // =========================
        Role::firstOrCreate([
            'name' => 'assistant'
        ]);
    }
}