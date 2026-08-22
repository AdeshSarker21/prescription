<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advices', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->unsignedInteger('used_count')->default(0)->after('slug');
            $table->foreignId('created_by')->nullable()->after('used_count')->constrained('users')->nullOnDelete();
            $table->string('status')->default('active')->after('created_by');
            $table->index('status');
            $table->index('used_count');
        });
    }

    public function down(): void
    {
        Schema::table('advices', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['slug', 'used_count', 'created_by', 'status']);
        });
    }
};
