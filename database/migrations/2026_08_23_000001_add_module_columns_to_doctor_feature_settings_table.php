<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_feature_settings', function (Blueprint $table) {
            $table->boolean('module_prescription')->default(true)->after('treatment_plan');
            $table->boolean('module_patient_management')->default(true);
            $table->boolean('module_appointment')->default(true);
            $table->boolean('module_smart_serial')->default(false);
            $table->boolean('module_sms_notification')->default(true);
            $table->boolean('module_ai_assistant')->default(false);
            $table->boolean('module_reports_analytics')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('doctor_feature_settings', function (Blueprint $table) {
            $table->dropColumn([
                'module_prescription',
                'module_patient_management',
                'module_appointment',
                'module_smart_serial',
                'module_sms_notification',
                'module_ai_assistant',
                'module_reports_analytics',
            ]);
        });
    }
};
