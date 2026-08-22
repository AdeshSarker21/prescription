<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->text('pregnancy_safe')->nullable()->change();
            $table->text('alcohol_warning')->nullable()->change();
            $table->text('food_interaction')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->boolean('pregnancy_safe')->default(true)->change();
            $table->boolean('alcohol_warning')->default(false)->change();
            $table->string('food_interaction')->nullable()->comment('before/after food')->change();
        });
    }
};
