<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->string('name');
            $table->string('type'); // welcome, follow_up, appointment, custom
            $table->text('message');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('doctor_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_templates');
    }
};
