<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('version', 20)->default('1.0.0');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_core')->default(false)->comment('Core modules are always enabled');
            $table->string('route_prefix')->nullable()->comment('Primary route prefix for this module');
            $table->string('icon')->nullable()->comment('Sidebar icon key');
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable()->comment('Extra module config (sidebar, permissions, etc.)');
            $table->timestamps();

            $table->index('is_active');
            $table->index('is_core');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
