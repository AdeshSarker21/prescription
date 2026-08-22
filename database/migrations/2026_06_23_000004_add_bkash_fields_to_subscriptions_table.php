<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('billing_cycle');
            $table->string('transaction_id')->nullable()->after('payment_method');
            $table->string('sender_number')->nullable()->after('transaction_id');
            $table->text('payment_note')->nullable()->after('sender_number');
            $table->timestamp('approved_at')->nullable()->after('payment_note');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'payment_method',
                'transaction_id',
                'sender_number',
                'payment_note',
                'approved_at',
                'approved_by',
            ]);
        });
    }
};
