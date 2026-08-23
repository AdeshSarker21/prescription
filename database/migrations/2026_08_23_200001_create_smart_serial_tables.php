<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serial_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->date('session_date');
            $table->string('session_label')->nullable();
            $table->enum('status', ['active', 'paused', 'closed'])->default('active');
            $table->integer('current_serial')->default(0);
            $table->integer('total_patients')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['doctor_id', 'session_date']);
        });

        Schema::create('patient_queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('serial_session_id')->constrained('serial_sessions')->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->integer('serial_number');
            $table->enum('status', ['waiting', 'called', 'in_consultation', 'completed', 'cancelled', 'no_show'])->default('waiting');
            $table->enum('priority', ['normal', 'urgent', 'vip', 'emergency'])->default('normal');
            $table->text('notes')->nullable();
            $table->timestamp('called_at')->nullable();
            $table->timestamp('consultation_started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['serial_session_id', 'status']);
            $table->index(['doctor_id', 'status']);
        });

        Schema::create('smart_serial_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('auto_call_next')->default(false);
            $table->boolean('show_in_appointment')->default(true);
            $table->boolean('allow_priority')->default(true);
            $table->integer('max_queue_size')->default(50);
            $table->boolean('serial_reset_daily')->default(true);
            $table->boolean('notification_enabled')->default(true);
            $table->timestamps();

            $table->unique('doctor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_queues');
        Schema::dropIfExists('serial_sessions');
        Schema::dropIfExists('smart_serial_settings');
    }
};
