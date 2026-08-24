<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $smartSerial = DB::table('modules')->where('slug', 'smart_serial')->first();

        if (!$smartSerial) {
            return;
        }

        DB::table('module_permissions')->updateOrInsert(
            [
                'module_id' => $smartSerial->id,
                'name' => 'prepare',
                'guard_name' => 'web',
            ],
            [
                'description' => 'Prepare next patient in queue and trigger voice announcement',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        $smartSerial = DB::table('modules')->where('slug', 'smart_serial')->first();

        if ($smartSerial) {
            DB::table('module_permissions')
                ->where('module_id', $smartSerial->id)
                ->where('name', 'prepare')
                ->delete();
        }
    }
};
