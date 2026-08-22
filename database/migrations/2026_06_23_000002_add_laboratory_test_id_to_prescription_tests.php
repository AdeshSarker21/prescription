<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescription_tests', function (Blueprint $table) {
            $table->foreignId('laboratory_test_id')->nullable()->constrained('laboratory_tests')->nullOnDelete()->after('prescription_id');
        });
    }

    public function down(): void
    {
        Schema::table('prescription_tests', function (Blueprint $table) {
            $table->dropForeign(['laboratory_test_id']);
            $table->dropColumn('laboratory_test_id');
        });
    }
};
