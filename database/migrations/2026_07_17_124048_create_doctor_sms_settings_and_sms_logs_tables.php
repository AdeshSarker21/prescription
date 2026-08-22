<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_sms_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->boolean('sms_enabled')->default(false);
            $table->string('api_url');
            $table->string('api_key')->nullable();
            $table->string('sender_id')->nullable();
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->integer('reminder_days_before')->default(1);
            $table->text('sms_template')->nullable();
            $table->timestamps();

            $table->unique('doctor_id');
            $table->foreign('doctor_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('prescription_id')->nullable();
            $table->string('recipient_phone');
            $table->text('message');
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->text('error_message')->nullable();
            $table->string('type')->default('follow_up'); // follow_up, custom
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index('doctor_id');
            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
        Schema::dropIfExists('doctor_sms_settings');
    }
};
