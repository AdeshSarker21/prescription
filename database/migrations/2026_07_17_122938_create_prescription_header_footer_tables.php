<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_headers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('content');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('prescription_footers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('content');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('doctor_prescription_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->boolean('header_enabled')->default(false);
            $table->unsignedBigInteger('header_id')->nullable();
            $table->boolean('footer_enabled')->default(false);
            $table->unsignedBigInteger('footer_id')->nullable();
            $table->timestamps();

            $table->unique('doctor_id');
            $table->foreign('doctor_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('header_id')->references('id')->on('prescription_headers')->nullOnDelete();
            $table->foreign('footer_id')->references('id')->on('prescription_footers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_prescription_settings');
        Schema::dropIfExists('prescription_footers');
        Schema::dropIfExists('prescription_headers');
    }
};
