<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->decimal('amount_paid', 10, 2)->nullable()->after('payment_note')->comment('Amount paid for this subscription');
            $table->foreignId('renewed_from')->nullable()->constrained('subscriptions')->nullOnDelete()->after('amount_paid')->comment('Previous subscription this renewed from');
            $table->timestamp('activated_at')->nullable()->after('renewed_from')->comment('When subscription was actually activated');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['renewed_from']);
            $table->dropColumn(['amount_paid', 'renewed_from', 'activated_at']);
        });
    }
};
