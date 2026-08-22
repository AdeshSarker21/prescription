<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_feature_settings', function (Blueprint $table) {
            $table->boolean('procedure')->default(false)->after('anesthesia');
            $table->boolean('treatment_plan')->default(false)->after('procedure');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_feature_settings', function (Blueprint $table) {
            $table->dropColumn(['procedure', 'treatment_plan']);
        });
    }
};
