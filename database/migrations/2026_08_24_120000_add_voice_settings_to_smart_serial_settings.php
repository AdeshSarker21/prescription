<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('smart_serial_settings', function (Blueprint $table) {
            $table->string('tts_provider')->default('google_translate')->after('display_enabled');
            $table->string('tts_api_key')->nullable()->after('tts_provider');
            $table->string('tts_voice')->default('bn-BD')->after('tts_api_key');
            $table->decimal('tts_speed', 3, 1)->default(1.0)->after('tts_voice');
            $table->decimal('tts_volume', 3, 1)->default(1.0)->after('tts_speed');
            $table->string('tts_language')->default('bn-BD')->after('tts_volume');
            $table->boolean('tts_fallback_enabled')->default(true)->after('tts_language');
        });
    }

    public function down(): void
    {
        Schema::table('smart_serial_settings', function (Blueprint $table) {
            $table->dropColumn([
                'tts_provider', 'tts_api_key', 'tts_voice',
                'tts_speed', 'tts_volume', 'tts_language', 'tts_fallback_enabled',
            ]);
        });
    }
};
