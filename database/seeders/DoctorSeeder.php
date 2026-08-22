<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        $doctors = [
            ['name' => 'Dr. Sarah Smith',     'email' => 'sarah.smith@clinic.com'],
            ['name' => 'Dr. John Williams',   'email' => 'john.williams@clinic.com'],
            ['name' => 'Dr. Emily Brown',     'email' => 'emily.brown@clinic.com'],
            ['name' => 'Dr. Michael Lee',     'email' => 'michael.lee@clinic.com'],
            ['name' => 'Dr. Lisa Garcia',     'email' => 'lisa.garcia@clinic.com'],
            ['name' => 'Dr. David Wilson',    'email' => 'david.wilson@clinic.com'],
            ['name' => 'Dr. Rachel Kim',      'email' => 'rachel.kim@clinic.com'],
            ['name' => 'Dr. James Taylor',    'email' => 'james.taylor@clinic.com'],
        ];

        $basic = Plan::where('slug', 'basic')->first();

        foreach ($doctors as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                ]
            );
            $user->assignRole('doctor');

            if ($basic && $user->subscriptions()->count() === 0) {
                $user->subscriptions()->create([
                    'plan_id' => $basic->id,
                    'status' => 'active',
                    'billing_cycle' => 'monthly',
                    'starts_at' => now(),
                    'ends_at' => now()->addMonth(),
                ]);
            }
        }

        $this->command->info('Seeded ' . count($doctors) . ' doctors with Basic plan.');
    }
}
