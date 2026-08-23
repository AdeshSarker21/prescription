<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $smartSerial = DB::table('modules')->where('slug', 'smart_serial')->first();

        if (!$smartSerial) {
            return;
        }

        $permissions = [
            ['name' => 'view',               'description' => 'View smart serial queue and sessions'],
            ['name' => 'create_serial',      'description' => 'Create new serial sessions and add patients to queue'],
            ['name' => 'edit_serial',        'description' => 'Edit serial queue entries and patient details'],
            ['name' => 'cancel_serial',      'description' => 'Cancel patient queue entries'],
            ['name' => 'call_next',          'description' => 'Call next patient in queue'],
            ['name' => 'recall',             'description' => 'Recall a previously called patient'],
            ['name' => 'skip',               'description' => 'Skip a patient in the queue'],
            ['name' => 'complete',           'description' => 'Mark patient consultation as complete'],
            ['name' => 'emergency',          'description' => 'Mark emergency priority and override queue'],
            ['name' => 'voice_announcement', 'description' => 'Trigger voice announcements for patient calls'],
            ['name' => 'display',            'description' => 'Control public display screen output'],
            ['name' => 'reports',            'description' => 'View smart serial reports and analytics'],
        ];

        foreach ($permissions as $index => $perm) {
            DB::table('module_permissions')->updateOrInsert(
                [
                    'module_id' => $smartSerial->id,
                    'name' => $perm['name'],
                    'guard_name' => 'web',
                ],
                [
                    'description' => $perm['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        $smartSerial = DB::table('modules')->where('slug', 'smart_serial')->first();

        if ($smartSerial) {
            DB::table('module_permissions')
                ->where('module_id', $smartSerial->id)
                ->whereIn('name', [
                    'view', 'create_serial', 'edit_serial', 'cancel_serial',
                    'call_next', 'recall', 'skip', 'complete',
                    'emergency', 'voice_announcement', 'display', 'reports',
                ])
                ->delete();
        }
    }
};
