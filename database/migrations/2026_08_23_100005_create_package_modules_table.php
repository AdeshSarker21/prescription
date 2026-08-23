<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_included')->default(true)->comment('Whether module is included in this plan');
            $table->json('settings')->nullable()->comment('Plan-specific module config e.g. limits');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['plan_id', 'module_id']);
            $table->index('module_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_modules');
    }
};
