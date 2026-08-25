<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('smart_serial_announcement_histories')) {
            Schema::create('smart_serial_announcement_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('serial_session_id')->constrained('serial_sessions')->cascadeOnDelete();
                $table->foreignId('patient_queue_id')->constrained('patient_queues')->cascadeOnDelete();
                $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
                $table->string('announcement_type');
                $table->text('text_spoken');
                $table->string('tts_provider_used');
                $table->string('audio_cache_key')->nullable();
                $table->boolean('success')->default(true);
                $table->text('error_message')->nullable();
                $table->timestamp('announced_at');
                $table->timestamps();

                $table->index(['serial_session_id', 'patient_queue_id', 'announcement_type'], 'ssah_session_queue_type_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('smart_serial_announcement_histories');
    }
};
