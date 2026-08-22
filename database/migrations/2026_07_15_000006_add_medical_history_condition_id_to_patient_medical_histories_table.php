<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_medical_histories', function (Blueprint $table) {
            $table->foreignId('medical_history_condition_id')->nullable()->after('patient_id')->constrained('medical_history_conditions')->nullOnDelete();
            $table->index('medical_history_condition_id');
        });
    }

    public function down(): void
    {
        Schema::table('patient_medical_histories', function (Blueprint $table) {
            $table->dropForeign(['medical_history_condition_id']);
            $table->dropColumn('medical_history_condition_id');
        });
    }
};
