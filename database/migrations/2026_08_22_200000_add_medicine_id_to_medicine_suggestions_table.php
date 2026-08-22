<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicine_suggestions', function (Blueprint $table) {
            $table->foreignId('medicine_id')->nullable()->constrained('medicines')->nullOnDelete()->after('admin_notes');
        });
    }

    public function down(): void
    {
        Schema::table('medicine_suggestions', function (Blueprint $table) {
            $table->dropForeign(['medicine_id']);
            $table->dropColumn('medicine_id');
        });
    }
};
