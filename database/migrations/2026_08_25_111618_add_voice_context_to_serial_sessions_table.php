<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('serial_sessions', function (Blueprint $table) {
            $table->string('pending_voice_text')->nullable()->after('pending_queue_id');
            $table->string('pending_patient_name')->nullable()->after('pending_voice_text');
            $table->string('pending_patient_gender')->nullable()->default('male')->after('pending_patient_name');
            $table->unsignedBigInteger('pending_patient_id')->nullable()->after('pending_patient_gender');
        });
    }

    public function down(): void
    {
        Schema::table('serial_sessions', function (Blueprint $table) {
            $table->dropColumn(['pending_voice_text', 'pending_patient_name', 'pending_patient_gender', 'pending_patient_id']);
        });
    }
};
