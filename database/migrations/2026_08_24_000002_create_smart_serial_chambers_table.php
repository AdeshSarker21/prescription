<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('smart_serial_chambers')) {
            Schema::create('smart_serial_chambers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('doctor_id')->constrained('users')->cascadeOnDelete();
                $table->string('name');
                $table->string('chamber_number')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('serial_prefix', 20)->default('');
                $table->integer('daily_starting_number')->default(1);
                $table->text('description')->nullable();
                $table->timestamps();

                $table->unique(['doctor_id', 'chamber_number']);
            });
        }

        if (!Schema::hasColumn('serial_sessions', 'chamber_id')) {
            Schema::table('serial_sessions', function (Blueprint $table) {
                $table->foreignId('chamber_id')->nullable()->after('doctor_id')->constrained('smart_serial_chambers')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('serial_sessions', function (Blueprint $table) {
            $table->dropForeign(['chamber_id']);
            $table->dropColumn('chamber_id');
        });

        Schema::dropIfExists('smart_serial_chambers');
    }
};
