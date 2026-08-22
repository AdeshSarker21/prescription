<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('used_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('name');
            $table->index('status');
            $table->index('used_count');
        });

        Schema::create('treatment_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('used_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('name');
            $table->index('status');
            $table->index('used_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_plans');
        Schema::dropIfExists('procedures');
    }
};
