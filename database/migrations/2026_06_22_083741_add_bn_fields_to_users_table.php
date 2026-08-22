<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name_bn')->nullable()->after('name');
            $table->string('specialization_bn')->nullable()->after('specialization');
            $table->string('qualification_bn')->nullable()->after('qualification');
            $table->string('designation_title_bn')->nullable()->after('designation_title');
            $table->string('affiliated_hospital_bn')->nullable()->after('affiliated_hospital');
            $table->json('sub_specialties_bn')->nullable()->after('sub_specialties');
            $table->string('clinic_name_bn')->nullable()->after('clinic_name');
            $table->text('address_bn')->nullable()->after('address');
            $table->string('prescription_heading_bn')->nullable()->after('prescription_heading');
            $table->string('prescription_slogan_bn')->nullable()->after('prescription_slogan');
            $table->string('emergency_contact_bn')->nullable()->after('emergency_contact');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'name_bn',
                'specialization_bn',
                'qualification_bn',
                'designation_title_bn',
                'affiliated_hospital_bn',
                'sub_specialties_bn',
                'clinic_name_bn',
                'address_bn',
                'prescription_heading_bn',
                'prescription_slogan_bn',
                'emergency_contact_bn',
            ]);
        });
    }
};
