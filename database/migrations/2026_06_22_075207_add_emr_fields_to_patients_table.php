<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('patient_number')->nullable()->unique()->after('id');
            $table->string('blood_group')->nullable()->after('gender');
            $table->decimal('height', 5, 1)->nullable()->after('blood_group');
            $table->decimal('weight', 5, 1)->nullable()->after('height');
            $table->string('emergency_contact')->nullable()->after('phone');
            $table->string('occupation')->nullable()->after('emergency_contact');
            $table->string('marital_status')->nullable()->after('occupation');
            $table->string('national_id')->nullable()->after('marital_status');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'patient_number', 'blood_group', 'height', 'weight',
                'emergency_contact', 'occupation', 'marital_status', 'national_id',
            ]);
        });
    }
};
