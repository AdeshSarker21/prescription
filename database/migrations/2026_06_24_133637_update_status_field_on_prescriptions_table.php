<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('status')->default('investigation_pending')->change();
        });

        DB::table('prescriptions')
            ->where('status', 'active')
            ->update(['status' => 'investigation_pending']);
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->string('status')->default('active')->change();
        });
    }
};
