<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laboratory_tests', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('test_name');
            $table->foreignId('created_by')->nullable()->after('used_count')->constrained('users')->nullOnDelete();
            $table->string('status')->default('active')->after('created_by');
            $table->index('status');
            $table->index('test_name');
        });
    }

    public function down(): void
    {
        Schema::table('laboratory_tests', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['slug', 'created_by', 'status']);
        });
    }
};
