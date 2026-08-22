<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('generic_name')->nullable();
            $table->string('brand_name')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('medicine_categories')->nullOnDelete();
            $table->string('strength')->nullable();
            $table->text('active_ingredients')->nullable();
            $table->text('salt_composition')->nullable();
            $table->string('company_name')->nullable();
            $table->string('country')->nullable();
            $table->boolean('batch_required')->default(false);
            $table->string('adult_dose')->nullable();
            $table->string('child_dose')->nullable();
            $table->string('max_daily_dose')->nullable();
            $table->text('duration_recommendation')->nullable();
            $table->text('side_effects')->nullable();
            $table->text('contraindications')->nullable();
            $table->boolean('pregnancy_safe')->default(true);
            $table->text('allergy_warning')->nullable();
            $table->text('drug_interaction_notes')->nullable();
            $table->text('usage_instructions')->nullable();
            $table->string('food_interaction')->nullable()->comment('before/after food');
            $table->boolean('alcohol_warning')->default(false);
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_global')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
