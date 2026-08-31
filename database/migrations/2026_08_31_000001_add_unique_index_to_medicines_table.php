<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove existing duplicates before adding unique constraint
        $duplicates = DB::select("
            SELECT MIN(id) as keep_id,
                   LOWER(name) as lname,
                   LOWER(COALESCE(strength, '')) as lstrength,
                   LOWER(COALESCE(generic_name, '')) as lgeneric,
                   COALESCE(tenant_id, 0) as tid
            FROM medicines
            GROUP BY LOWER(name), LOWER(COALESCE(strength, '')), LOWER(COALESCE(generic_name, '')), COALESCE(tenant_id, 0)
            HAVING COUNT(*) > 1
        ");

        foreach ($duplicates as $dup) {
            $keepId = $dup->keep_id;
            $dupeIds = DB::select("
                SELECT id FROM medicines
                WHERE LOWER(name) = ?
                  AND LOWER(COALESCE(strength, '')) = ?
                  AND LOWER(COALESCE(generic_name, '')) = ?
                  AND COALESCE(tenant_id, 0) = ?
                  AND id != ?
            ", [$dup->lname, $dup->lstrength, $dup->lgeneric, $dup->tid, $keepId]);

            $dupeIdArray = array_column($dupeIds, 'id');

            if (!empty($dupeIdArray)) {
                $placeholders = implode(',', array_fill(0, count($dupeIdArray), '?'));

                DB::update("UPDATE prescription_items SET medicine_id = ? WHERE medicine_id IN ({$placeholders})", array_merge([$keepId], $dupeIdArray));
                DB::update("UPDATE medicine_suggestions SET medicine_id = ? WHERE medicine_id IN ({$placeholders})", array_merge([$keepId], $dupeIdArray));
                DB::delete("DELETE FROM medicines WHERE id IN ({$placeholders})", $dupeIdArray);
            }
        }

        // Add generated columns for case-insensitive unique matching
        Schema::table('medicines', function (Blueprint $table) {
            $table->string('name_lower')->virtualAs('LOWER(name)')->stored();
            $table->string('strength_lower')->virtualAs('LOWER(COALESCE(strength, \'\'))')->stored();
            $table->string('generic_name_lower')->virtualAs('LOWER(COALESCE(generic_name, \'\'))')->stored();
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->unique(
                ['name_lower', 'strength_lower', 'generic_name_lower', 'tenant_id'],
                'medicines_identity_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropIndex('medicines_identity_unique');
            $table->dropColumn(['name_lower', 'strength_lower', 'generic_name_lower']);
        });
    }
};
