<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investigation_group_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('investigation_groups')->cascadeOnDelete();
            $table->string('parameter_name');
            $table->string('parameter_name_bn')->nullable();
            $table->string('unit')->nullable();
            $table->string('reference_range')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investigation_group_parameters');
    }
};
