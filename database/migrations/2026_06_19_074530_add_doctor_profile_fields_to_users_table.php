<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('specialization')->nullable()->after('phone');
            $table->string('qualification')->nullable()->after('specialization');
            $table->string('license_number')->nullable()->after('qualification');
            $table->integer('experience_years')->nullable()->after('license_number');
            $table->string('clinic_name')->nullable()->after('experience_years');
            $table->string('address')->nullable()->after('clinic_name');
            $table->string('visiting_hours')->nullable()->after('address');
            $table->string('status')->default('active')->after('visiting_hours');

            if (!Schema::hasColumn('users', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'specialization', 'qualification',
                'license_number', 'experience_years',
                'clinic_name', 'address', 'visiting_hours',
                'status', 'tenant_id',
            ]);
        });
    }
};
