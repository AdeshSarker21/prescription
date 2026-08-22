<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_advices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('advice_id')->constrained('advices')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['prescription_id', 'advice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_advices');
    }
};
