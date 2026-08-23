<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\ModulePermission;
use App\Models\Plan;
use App\Models\PackageModule;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'name' => 'Prescription',
                'slug' => 'prescription',
                'description' => 'Digital prescription creation, editing, and printing',
                'version' => '1.0.0',
                'is_active' => true,
                'is_core' => true,
                'route_prefix' => 'doctor/prescriptions',
                'icon' => 'document-text',
                'sort_order' => 1,
                'metadata' => [
                    'sidebar' => [
                        'admin' => ['route' => 'admin.dashboard', 'label' => 'Prescriptions', 'group' => 'core'],
                        'doctor' => ['route' => 'doctor.prescriptions.index', 'label' => 'Prescriptions', 'group' => 'core'],
                    ],
                ],
                'plans' => ['basic', 'pro', 'premium'],
            ],
            [
                'name' => 'Patient Management',
                'slug' => 'patient_management',
                'description' => 'Patient records, history, allergies, and medical data',
                'version' => '1.0.0',
                'is_active' => true,
                'is_core' => true,
                'route_prefix' => 'doctor/patients',
                'icon' => 'users',
                'sort_order' => 2,
                'metadata' => [
                    'sidebar' => [
                        'admin' => ['route' => 'admin.patients.index', 'label' => 'Patients', 'group' => 'core'],
                        'doctor' => ['route' => 'doctor.patients.index', 'label' => 'Patients', 'group' => 'core'],
                        'assistant' => ['route' => 'assistant.patients.create', 'label' => 'New Patient', 'group' => 'core'],
                    ],
                ],
                'plans' => ['basic', 'pro', 'premium'],
            ],
            [
                'name' => 'Appointment',
                'slug' => 'appointment',
                'description' => 'Patient appointments, scheduling, and queue management',
                'version' => '1.0.0',
                'is_active' => true,
                'is_core' => true,
                'route_prefix' => 'doctor/appointments',
                'icon' => 'calendar',
                'sort_order' => 3,
                'metadata' => [
                    'sidebar' => [
                        'doctor' => ['route' => 'doctor.appointments.index', 'label' => 'Appointments', 'group' => 'core'],
                        'assistant' => ['route' => 'assistant.appointments.index', 'label' => 'Appointments', 'group' => 'core'],
                    ],
                ],
                'plans' => ['basic', 'pro', 'premium'],
            ],
            [
                'name' => 'Smart Serial Management',
                'slug' => 'smart_serial',
                'description' => 'Patient queue/token management with serial numbering',
                'version' => '1.0.0',
                'is_active' => true,
                'is_core' => false,
                'route_prefix' => 'doctor/smart-serial',
                'icon' => 'queue-list',
                'sort_order' => 4,
                'metadata' => [
                    'sidebar' => [
                        'doctor' => ['route' => 'doctor.smart-serial.index', 'label' => 'Smart Serial', 'group' => 'modules'],
                        'assistant' => ['route' => 'assistant.smart-serial.index', 'label' => 'Smart Serial', 'group' => 'modules'],
                    ],
                    'permissions' => ['smart-serial-manage'],
                ],
                'plans' => ['pro', 'premium'],
            ],
            [
                'name' => 'SMS & Notification',
                'slug' => 'sms_notification',
                'description' => 'SMS notifications, reminders, and messaging center',
                'version' => '1.0.0',
                'is_active' => true,
                'is_core' => true,
                'route_prefix' => 'doctor/sms-center',
                'icon' => 'chat-bubble-left',
                'sort_order' => 5,
                'metadata' => [
                    'sidebar' => [
                        'admin' => ['route' => 'admin.sms-settings.index', 'label' => 'SMS Settings', 'group' => 'settings'],
                        'doctor' => ['route' => 'doctor.sms-center.index', 'label' => 'SMS Center', 'group' => 'tools'],
                    ],
                ],
                'plans' => ['pro', 'premium'],
            ],
            [
                'name' => 'AI Assistant',
                'slug' => 'ai_assistant',
                'description' => 'AI-powered diagnosis suggestions, drug interaction checks, and clinical decision support',
                'version' => '1.0.0',
                'is_active' => true,
                'is_core' => true,
                'route_prefix' => 'doctor/ai-assistant',
                'icon' => 'cpu-chip',
                'sort_order' => 6,
                'metadata' => [
                    'sidebar' => [
                        'doctor' => ['route' => 'doctor.ai-assistant', 'label' => 'AI Medical Assistant', 'group' => 'tools'],
                    ],
                ],
                'plans' => ['pro', 'premium'],
            ],
            [
                'name' => 'Reports & Analytics',
                'slug' => 'reports_analytics',
                'description' => 'Patient reports, prescription analytics, and monthly statistics',
                'version' => '1.0.0',
                'is_active' => true,
                'is_core' => true,
                'route_prefix' => 'doctor/reports',
                'icon' => 'chart-bar',
                'sort_order' => 7,
                'metadata' => [
                    'sidebar' => [
                        'doctor' => ['route' => 'doctor.reports', 'label' => 'Reports', 'group' => 'tools'],
                    ],
                ],
                'plans' => ['pro', 'premium'],
            ],
        ];

        foreach ($modules as $moduleData) {
            $planSlugs = $moduleData['plans'] ?? [];
            unset($moduleData['plans']);

            $module = Module::updateOrCreate(
                ['slug' => $moduleData['slug']],
                $moduleData
            );

            $plans = Plan::whereIn('slug', $planSlugs)->get();
            foreach ($plans as $plan) {
                PackageModule::updateOrCreate(
                    ['plan_id' => $plan->id, 'module_id' => $module->id],
                    [
                        'is_included' => true,
                        'sort_order' => $module->sort_order,
                    ]
                );
            }

            if (!empty($moduleData['metadata']['permissions'])) {
                foreach ($moduleData['metadata']['permissions'] as $permName) {
                    ModulePermission::updateOrCreate(
                        ['module_id' => $module->id, 'name' => $permName, 'guard_name' => 'web'],
                        ['description' => "Access to {$module->name}: {$permName}"]
                    );
                }
            }
        }

        $this->command->info('Seeded ' . count($modules) . ' modules with plan mappings.');
    }
}
