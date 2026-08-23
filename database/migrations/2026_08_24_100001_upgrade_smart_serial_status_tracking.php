<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add missing columns to smart_serial_settings
        Schema::table('smart_serial_settings', function (Blueprint $table) {
            if (!Schema::hasColumn('smart_serial_settings', 'starting_serial_number')) {
                $table->integer('starting_serial_number')->default(1)->after('max_queue_size');
            }
            if (!Schema::hasColumn('smart_serial_settings', 'auto_increment')) {
                $table->boolean('auto_increment')->default(true)->after('starting_serial_number');
            }
            if (!Schema::hasColumn('smart_serial_settings', 'prefix')) {
                $table->string('prefix', 20)->default('')->after('auto_increment');
            }
            if (!Schema::hasColumn('smart_serial_settings', 'max_serial')) {
                $table->integer('max_serial')->default(999)->after('prefix');
            }
            if (!Schema::hasColumn('smart_serial_settings', 'emergency_priority')) {
                $table->boolean('emergency_priority')->default(true)->after('max_serial');
            }
            if (!Schema::hasColumn('smart_serial_settings', 'queue_mode')) {
                $table->string('queue_mode', 20)->default('serial')->after('emergency_priority');
            }
            if (!Schema::hasColumn('smart_serial_settings', 'voice_enabled')) {
                $table->boolean('voice_enabled')->default(false)->after('queue_mode');
            }
            if (!Schema::hasColumn('smart_serial_settings', 'display_enabled')) {
                $table->boolean('display_enabled')->default(false)->after('voice_enabled');
            }
        });

        // Add formatted_serial and new timestamps to patient_queues
        Schema::table('patient_queues', function (Blueprint $table) {
            if (!Schema::hasColumn('patient_queues', 'formatted_serial')) {
                $table->string('formatted_serial', 20)->nullable()->after('serial_number');
            }
            if (!Schema::hasColumn('patient_queues', 'prepared_at')) {
                $table->timestamp('prepared_at')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('patient_queues', 'entered_at')) {
                $table->timestamp('entered_at')->nullable()->after('prepared_at');
            }
        });

        // Update patient_queues status enum to include new statuses
        // SQLite doesn't support ALTER COLUMN, so we rely on application-level validation
        // The enum values are enforced in the model, not the DB

        // Add unique constraint for doctor + chamber + date on serial_sessions
        Schema::table('serial_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('serial_sessions', 'daily_serial_counter')) {
                $table->integer('daily_serial_counter')->default(0)->after('total_patients');
            }
        });

        // Create serial_status_logs table for permanent status history
        if (!Schema::hasTable('serial_status_logs')) {
            Schema::create('serial_status_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('patient_queue_id')->constrained('patient_queues')->cascadeOnDelete();
                $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('serial_session_id')->constrained('serial_sessions')->cascadeOnDelete();
                $table->string('formatted_serial', 20);
                $table->string('old_status', 30)->nullable();
                $table->string('new_status', 30);
                $table->text('notes')->nullable();
                $table->string('changed_by', 30)->nullable()->comment('doctor, assistant, system');
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

                $table->index(['patient_queue_id', 'created_at']);
                $table->index(['doctor_id', 'created_at']);
                $table->index(['serial_session_id', 'new_status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('serial_status_logs');

        Schema::table('patient_queues', function (Blueprint $table) {
            $table->dropColumn(['formatted_serial', 'prepared_at', 'entered_at']);
        });

        Schema::table('serial_sessions', function (Blueprint $table) {
            $table->dropColumn('daily_serial_counter');
        });

        Schema::table('smart_serial_settings', function (Blueprint $table) {
            $table->dropColumn([
                'starting_serial_number', 'auto_increment', 'prefix',
                'max_serial', 'emergency_priority', 'queue_mode',
                'voice_enabled', 'display_enabled',
            ]);
        });
    }
};
