<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('serial_sessions', function (Blueprint $table) {
            $table->string('pending_announcement')->nullable()->after('daily_serial_counter');
        });
    }

    public function down(): void
    {
        Schema::table('serial_sessions', function (Blueprint $table) {
            $table->dropColumn('pending_announcement');
        });
    }
};
