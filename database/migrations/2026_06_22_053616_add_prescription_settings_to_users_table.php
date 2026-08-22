<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('designation_title')->nullable()->after('qualification');
            $table->string('affiliated_hospital')->nullable()->after('designation_title');
            $table->json('sub_specialties')->nullable()->after('affiliated_hospital');
            $table->json('chambers')->nullable()->after('sub_specialties');
            $table->string('emergency_contact')->nullable()->after('chambers');
            $table->string('prescription_heading')->nullable()->after('emergency_contact');
            $table->string('prescription_slogan')->nullable()->after('prescription_heading');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'designation_title',
                'affiliated_hospital',
                'sub_specialties',
                'chambers',
                'emergency_contact',
                'prescription_heading',
                'prescription_slogan',
            ]);
        });
    }
};
