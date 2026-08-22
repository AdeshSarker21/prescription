<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->string('type')->default('medicine')->after('prescription_id');
            $table->foreignId('seal_id')->nullable()->after('type')->constrained('clinical_seals')->nullOnDelete();
            $table->text('seal_text')->nullable()->after('seal_id');
        });
    }

    public function down(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropForeign(['seal_id']);
            $table->dropColumn(['type', 'seal_id', 'seal_text']);
        });
    }
};
