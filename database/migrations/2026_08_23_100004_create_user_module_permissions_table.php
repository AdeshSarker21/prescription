<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_module_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_permission_id')->constrained('module_permissions')->cascadeOnDelete();
            $table->boolean('is_granted')->default(true);
            $table->string('granted_by')->nullable()->comment('admin user_id who granted this');
            $table->timestamp('granted_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'module_permission_id']);
            $table->index('module_permission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_module_permissions');
    }
};
