<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->foreignId('medicine_suggestion_id')->nullable()->constrained('medicine_suggestions')->nullOnDelete()->after('seal_details');
        });
    }

    public function down(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropForeign(['medicine_suggestion_id']);
            $table->dropColumn('medicine_suggestion_id');
        });
    }
};
