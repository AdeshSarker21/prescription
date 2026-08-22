<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('procedure_id')->nullable()->constrained('procedures')->nullOnDelete();
            $table->string('procedure_name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('procedure_id');
        });

        Schema::create('prescription_treatment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('treatment_plan_id')->nullable()->constrained('treatment_plans')->nullOnDelete();
            $table->string('treatment_plan_name');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('treatment_plan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_treatment_plans');
        Schema::dropIfExists('prescription_procedures');
    }
};
