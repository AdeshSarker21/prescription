<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->text('indication')->nullable()->after('company_name');
            $table->text('composition')->nullable()->after('indication');
            $table->text('pharmacology')->nullable()->after('composition');
            $table->text('overdose_effects')->nullable()->after('pharmacology');
            $table->text('therapeutic_class')->nullable()->after('overdose_effects');
            $table->text('storage_conditions')->nullable()->after('therapeutic_class');
            $table->string('medex_url')->nullable()->after('storage_conditions');
        });
    }

    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn([
                'indication', 'composition', 'pharmacology',
                'overdose_effects', 'therapeutic_class', 'storage_conditions', 'medex_url',
            ]);
        });
    }
};
