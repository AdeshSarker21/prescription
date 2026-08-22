<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class ResetUserTableSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks for truncation
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Truncate related tables in order
        DB::table('doctor_assistants')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::table('subscriptions')->truncate();
        DB::table('users')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        // Ensure roles exist
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'doctor']);
        Role::firstOrCreate(['name' => 'assistant']);

        // Get the basic plan
        $basic = Plan::where('slug', 'basic')->first();

        // ========================================
        // 1. SUPERADMIN USER
        // ========================================
        $admin = User::create([
            'name'              => 'Super Admin',
            'email'             => 'admin@clinic.com',
            'password'          => bcrypt('password'),
            'email_verified_at' => now(),
            'is_approved'       => true,
            'status'            => 'active',
        ]);
        $admin->assignRole('admin');

        if ($basic) {
            $admin->subscriptions()->create([
                'plan_id'       => $basic->id,
                'status'        => 'active',
                'billing_cycle' => 'yearly',
                'starts_at'     => now(),
                'ends_at'       => now()->addYear(),
            ]);
        }

        $this->command->info('✅ Superadmin created: admin@clinic.com / password');

        // ========================================
        // 2. DOCTOR USERS
        // ========================================
        $doctors = [
            [
                'name'            => 'Dr. Sarah Smith',
                'email'           => 'sarah.smith@clinic.com',
                'specialization'  => 'Cardiology',
                'qualification'   => 'MBBS, MD (Cardiology)',
                'license_number'  => 'MED-001',
                'experience_years'=> 12,
                'clinic_name'     => 'Heart Care Clinic',
                'phone'           => '+8801712345678',
            ],
            [
                'name'            => 'Dr. John Williams',
                'email'           => 'john.williams@clinic.com',
                'specialization'  => 'Neurology',
                'qualification'   => 'MBBS, MD (Neurology)',
                'license_number'  => 'MED-002',
                'experience_years'=> 8,
                'clinic_name'     => 'Neuro Care Center',
                'phone'           => '+8801712345679',
            ],
            [
                'name'            => 'Dr. Emily Brown',
                'email'           => 'emily.brown@clinic.com',
                'specialization'  => 'Pediatrics',
                'qualification'   => 'MBBS, DCH',
                'license_number'  => 'MED-003',
                'experience_years'=> 10,
                'clinic_name'     => 'Child Health Clinic',
                'phone'           => '+8801712345680',
            ],
            [
                'name'            => 'Dr. Michael Lee',
                'email'           => 'michael.lee@clinic.com',
                'specialization'  => 'Orthopedics',
                'qualification'   => 'MBBS, MS (Ortho)',
                'license_number'  => 'MED-004',
                'experience_years'=> 15,
                'clinic_name'     => 'Bone & Joint Clinic',
                'phone'           => '+8801712345681',
            ],
        ];

        $createdDoctors = [];

        foreach ($doctors as $data) {
            $user = User::create([
                'name'              => $data['name'],
                'email'             => $data['email'],
                'password'          => bcrypt('password'),
                'email_verified_at' => now(),
                'is_approved'       => true,
                'status'            => 'active',
                'specialization'    => $data['specialization'],
                'qualification'     => $data['qualification'],
                'license_number'    => $data['license_number'],
                'experience_years'  => $data['experience_years'],
                'clinic_name'       => $data['clinic_name'],
                'phone'             => $data['phone'],
            ]);
            $user->assignRole('doctor');

            if ($basic) {
                $user->subscriptions()->create([
                    'plan_id'       => $basic->id,
                    'status'        => 'active',
                    'billing_cycle' => 'monthly',
                    'starts_at'     => now(),
                    'ends_at'       => now()->addMonth(),
                ]);
            }

            $createdDoctors[] = $user;
        }

        $this->command->info('✅ ' . count($createdDoctors) . ' doctors created');

        // ========================================
        // 3. ASSISTANT USERS (assigned to doctors)
        // ========================================
        $assistants = [
            [
                'name'  => 'Nurse Fatima Ahmed',
                'email' => 'fatima.ahmed@clinic.com',
                'phone' => '+8801812345678',
            ],
            [
                'name'  => 'Nurse Aisha Khan',
                'email' => 'aisha.khan@clinic.com',
                'phone' => '+8801812345679',
            ],
        ];

        foreach ($assistants as $index => $data) {
            $user = User::create([
                'name'              => $data['name'],
                'email'             => $data['email'],
                'password'          => bcrypt('password'),
                'email_verified_at' => now(),
                'is_approved'       => true,
                'status'            => 'active',
                'phone'             => $data['phone'],
            ]);
            $user->assignRole('assistant');

            // Assign assistant to first two doctors
            $assignedDoctor = $createdDoctors[$index % count($createdDoctors)];
            $user->assignedDoctors()->attach($assignedDoctor->id);
        }

        $this->command->info('✅ ' . count($assistants) . ' assistants created and assigned to doctors');

        // ========================================
        // SUMMARY
        // ========================================
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('   USER TABLE RESET COMPLETE');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('Role: Superadmin');
        $this->command->info('  Email:    admin@clinic.com');
        $this->command->info('  Password: password');
        $this->command->newLine();
        $this->command->info('Role: Doctor (4 users)');
        foreach ($doctors as $d) {
            $this->command->info("  {$d['email']} / password");
        }
        $this->command->newLine();
        $this->command->info('Role: Assistant (2 users)');
        foreach ($assistants as $a) {
            $this->command->info("  {$a['email']} / password");
        }
        $this->command->info('═══════════════════════════════════════');
    }
}
