<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaint_prescription', function (Blueprint $table) {
            $table->foreignId('complaint_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->primary(['complaint_id', 'prescription_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaint_prescription');
    }
};
