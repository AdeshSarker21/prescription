<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_feature_settings', function (Blueprint $table) {
            $table->boolean('complaints')->default(true)->after('doctor_id');
            $table->boolean('tests')->default(true)->after('complaints');
            $table->boolean('medical_history')->default(true)->after('tests');
            $table->boolean('advice')->default(true)->after('medical_history');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_feature_settings', function (Blueprint $table) {
            $table->dropColumn(['complaints', 'tests', 'medical_history', 'advice']);
        });
    }
};
