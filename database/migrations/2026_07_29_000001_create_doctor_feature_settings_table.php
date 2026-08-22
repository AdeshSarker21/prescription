<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_feature_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->boolean('clinical_features')->default(false);
            $table->boolean('family_history')->default(false);
            $table->boolean('menstrual_history')->default(false);
            $table->boolean('drug_history')->default(false);
            $table->boolean('ot_note')->default(false);
            $table->boolean('anesthesia')->default(false);
            $table->timestamps();

            $table->unique('doctor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_feature_settings');
    }
};
