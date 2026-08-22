<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->json('family_history_data')->nullable()->after('spo2');
            $table->json('menstrual_history_data')->nullable()->after('family_history_data');
            $table->json('drug_history_data')->nullable()->after('menstrual_history_data');
            $table->json('ot_note_data')->nullable()->after('drug_history_data');
            $table->json('anesthesia_data')->nullable()->after('ot_note_data');
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn([
                'family_history_data',
                'menstrual_history_data',
                'drug_history_data',
                'ot_note_data',
                'anesthesia_data',
            ]);
        });
    }
};
