<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Module Registry
    |--------------------------------------------------------------------------
    |
    | Central registry for all optional modules. Each module is defined with
    | its metadata, access rules, and sidebar menu configuration.
    |
    | To add a new module:
    | 1. Add entry below with key matching the module slug
    | 2. Create a migration adding `module_slug` column to doctor_feature_settings
    | 3. Add the module key to PlanSeeder limitations
    | 4. Add permission to ModulePermissionSeeder
    | 5. Create routes, controller, views under the module namespace
    |
    */

    'modules' => [

        'prescription' => [
            'name'        => 'Prescription',
            'description' => 'Digital prescription creation, editing, and printing',
            'version'     => '1.0.0',
            'enabled'     => true,
            'core'        => true,
            'plan_key'    => 'prescription',
            'sidebar'     => [
                'admin' => [
                    'route'  => 'admin.dashboard',
                    'label'  => 'Prescriptions',
                    'icon'   => 'document-text',
                    'group'  => 'core',
                ],
                'doctor' => [
                    'route'  => 'doctor.prescriptions.index',
                    'label'  => 'Prescriptions',
                    'icon'   => 'document-text',
                    'group'  => 'core',
                ],
            ],
        ],

        'patient_management' => [
            'name'        => 'Patient Management',
            'description' => 'Patient records, history, allergies, and medical data',
            'version'     => '1.0.0',
            'enabled'     => true,
            'core'        => true,
            'plan_key'    => 'patient_management',
            'sidebar'     => [
                'admin' => [
                    'route'  => 'admin.patients.index',
                    'label'  => 'Patients',
                    'icon'   => 'users',
                    'group'  => 'core',
                ],
                'doctor' => [
                    'route'  => 'doctor.patients.index',
                    'label'  => 'Patients',
                    'icon'   => 'users',
                    'group'  => 'core',
                ],
                'assistant' => [
                    'route'  => 'assistant.patients.create',
                    'label'  => 'New Patient',
                    'icon'   => 'user-plus',
                    'group'  => 'core',
                ],
            ],
        ],

        'appointment' => [
            'name'        => 'Appointment',
            'description' => 'Patient appointments, scheduling, and queue management',
            'version'     => '1.0.0',
            'enabled'     => true,
            'core'        => true,
            'plan_key'    => 'appointment',
            'sidebar'     => [
                'admin' => [
                    'route'  => 'admin.dashboard',
                    'label'  => 'Appointments',
                    'icon'   => 'calendar',
                    'group'  => 'core',
                ],
                'doctor' => [
                    'route'  => 'doctor.appointments.index',
                    'label'  => 'Appointments',
                    'icon'   => 'calendar',
                    'group'  => 'core',
                ],
                'assistant' => [
                    'route'  => 'assistant.appointments.index',
                    'label'  => 'Appointments',
                    'icon'   => 'calendar',
                    'group'  => 'core',
                ],
            ],
        ],

        'smart_serial' => [
            'name'        => 'Smart Serial Management',
            'description' => 'Patient queue/token management with serial numbering',
            'version'     => '1.0.0',
            'enabled'     => true,
            'core'        => false,
            'plan_key'    => 'smart_serial',
            'permissions' => [
                'doctor'    => ['smart-serial-manage'],
                'assistant' => ['smart-serial-manage'],
            ],
            'sidebar'     => [
                'doctor' => [
                    'route'  => 'doctor.smart-serial.index',
                    'label'  => 'Smart Serial',
                    'icon'   => 'queue-list',
                    'group'  => 'modules',
                ],
                'assistant' => [
                    'route'  => 'assistant.smart-serial.index',
                    'label'  => 'Smart Serial',
                    'icon'   => 'queue-list',
                    'group'  => 'modules',
                ],
            ],
        ],

        'sms_notification' => [
            'name'        => 'SMS & Notification',
            'description' => 'SMS notifications, reminders, and messaging center',
            'version'     => '1.0.0',
            'enabled'     => true,
            'core'        => true,
            'plan_key'    => 'sms_notification',
            'sidebar'     => [
                'admin' => [
                    'route'  => 'admin.sms-settings.index',
                    'label'  => 'SMS Settings',
                    'icon'   => 'chat-bubble-left',
                    'group'  => 'settings',
                ],
                'doctor' => [
                    'route'  => 'doctor.sms-center.index',
                    'label'  => 'SMS Center',
                    'icon'   => 'chat-bubble-left',
                    'group'  => 'tools',
                ],
            ],
        ],

        'ai_assistant' => [
            'name'        => 'AI Assistant',
            'description' => 'AI-powered diagnosis suggestions, drug interaction checks, and clinical decision support',
            'version'     => '1.0.0',
            'enabled'     => true,
            'core'        => true,
            'plan_key'    => 'ai_assistant',
            'sidebar'     => [
                'doctor' => [
                    'route'  => 'doctor.ai-assistant',
                    'label'  => 'AI Medical Assistant',
                    'icon'   => 'cpu-chip',
                    'group'  => 'tools',
                ],
            ],
        ],

        'reports_analytics' => [
            'name'        => 'Reports & Analytics',
            'description' => 'Patient reports, prescription analytics, and monthly statistics',
            'version'     => '1.0.0',
            'enabled'     => true,
            'core'        => true,
            'plan_key'    => 'analytics',
            'sidebar'     => [
                'doctor' => [
                    'route'  => 'doctor.reports',
                    'label'  => 'Reports',
                    'icon'   => 'chart-bar',
                    'group'  => 'tools',
                ],
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Sidebar Icon Map
    |--------------------------------------------------------------------------
    |
    | SVG icon paths for sidebar menu items. Icons are referenced by key
    | in the module sidebar configuration above.
    |
    */

    'icons' => [
        'document-text' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
        'user-plus' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>',
        'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        'queue-list' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>',
        'chat-bubble-left' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
        'cpu-chip' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>',
        'chart-bar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        'cog' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
    ],

    /*
    |--------------------------------------------------------------------------
    | Module Groups
    |--------------------------------------------------------------------------
    |
    | Grouping for sidebar organization. Modules can be placed in groups
    | for visual separation in the sidebar.
    |
    */

    'groups' => [
        'core'     => 'Core Features',
        'modules'  => 'Modules',
        'tools'    => 'Tools',
        'settings' => 'Settings',
    ],

];
