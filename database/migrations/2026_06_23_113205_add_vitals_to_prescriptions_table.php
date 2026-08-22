<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->integer('bp_systolic')->nullable()->after('weight');
            $table->integer('bp_diastolic')->nullable()->after('bp_systolic');
            $table->integer('pulse_rate')->nullable()->after('bp_diastolic');
            $table->decimal('spo2', 4, 1)->nullable()->after('pulse_rate');
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn(['bp_systolic', 'bp_diastolic', 'pulse_rate', 'spo2']);
        });
    }
};
