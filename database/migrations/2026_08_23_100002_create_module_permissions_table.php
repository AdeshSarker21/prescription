<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->string('name')->comment('Permission slug e.g. smart-serial-manage');
            $table->string('guard_name')->default('web');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['module_id', 'name', 'guard_name']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_permissions');
    }
};
