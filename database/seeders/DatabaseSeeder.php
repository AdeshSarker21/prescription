<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PlanSeeder::class,
            ModuleSeeder::class,
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $basic = Plan::where('slug', 'basic')->first();

        $admin = User::firstOrCreate(
            ['email' => 'admin@clinic.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'is_approved' => true,
            ]
        );
        $admin->assignRole('admin');

        if ($basic && $admin->subscriptions()->count() === 0) {
            $admin->subscriptions()->create([
                'plan_id' => $basic->id,
                'status' => 'active',
                'billing_cycle' => 'yearly',
                'starts_at' => now(),
                'ends_at' => now()->addYear(),
            ]);
        }

        $this->call([
            DoctorSeeder::class,
            MedicineSeeder::class,
            DoctorPortalSeeder::class,
            InvestigationGroupSeeder::class,
            MasterDataSeeder::class,
            SmsTemplateSeeder::class,
        ]);
    }
}
