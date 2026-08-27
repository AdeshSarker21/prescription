<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctor_prescription_settings', function (Blueprint $table) {
            $table->string('paper_size')->default('A4')->after('footer_id');
            $table->decimal('paper_width_mm', 8, 2)->nullable()->after('paper_size');
            $table->decimal('paper_height_mm', 8, 2)->nullable()->after('paper_width_mm');

            $table->decimal('header_margin_top_mm', 8, 2)->default(0)->after('paper_height_mm');
            $table->decimal('header_margin_right_mm', 8, 2)->default(0)->after('header_margin_top_mm');
            $table->decimal('header_margin_bottom_mm', 8, 2)->default(0)->after('header_margin_right_mm');
            $table->decimal('header_margin_left_mm', 8, 2)->default(0)->after('header_margin_bottom_mm');

            $table->decimal('header_padding_top_mm', 8, 2)->default(5)->after('header_margin_left_mm');
            $table->decimal('header_padding_right_mm', 8, 2)->default(9)->after('header_padding_top_mm');
            $table->decimal('header_padding_bottom_mm', 8, 2)->default(5)->after('header_padding_right_mm');
            $table->decimal('header_padding_left_mm', 8, 2)->default(9)->after('header_padding_bottom_mm');

            $table->decimal('footer_margin_top_mm', 8, 2)->default(0)->after('header_padding_left_mm');
            $table->decimal('footer_margin_right_mm', 8, 2)->default(0)->after('footer_margin_top_mm');
            $table->decimal('footer_margin_bottom_mm', 8, 2)->default(0)->after('footer_margin_right_mm');
            $table->decimal('footer_margin_left_mm', 8, 2)->default(0)->after('footer_margin_bottom_mm');

            $table->decimal('footer_padding_top_mm', 8, 2)->default(4)->after('footer_margin_left_mm');
            $table->decimal('footer_padding_right_mm', 8, 2)->default(7)->after('footer_padding_top_mm');
            $table->decimal('footer_padding_bottom_mm', 8, 2)->default(4)->after('footer_padding_right_mm');
            $table->decimal('footer_padding_left_mm', 8, 2)->default(7)->after('footer_padding_bottom_mm');
        });
    }

    public function down(): void
    {
        Schema::table('doctor_prescription_settings', function (Blueprint $table) {
            $table->dropColumn([
                'paper_size', 'paper_width_mm', 'paper_height_mm',
                'header_margin_top_mm', 'header_margin_right_mm', 'header_margin_bottom_mm', 'header_margin_left_mm',
                'header_padding_top_mm', 'header_padding_right_mm', 'header_padding_bottom_mm', 'header_padding_left_mm',
                'footer_margin_top_mm', 'footer_margin_right_mm', 'footer_margin_bottom_mm', 'footer_margin_left_mm',
                'footer_padding_top_mm', 'footer_padding_right_mm', 'footer_padding_bottom_mm', 'footer_padding_left_mm',
            ]);
        });
    }
};
