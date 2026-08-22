<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->boolean('investigation_pending')->default(false)->after('status');
            $table->timestamp('treatment_started_at')->nullable()->after('investigation_pending');
        });
    }

    public function down(): void
    {
        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropColumn(['investigation_pending', 'treatment_started_at']);
        });
    }
};
