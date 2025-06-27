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
        // First ensure the stripe_id column exists in users table
        if (! Schema::hasColumn('users', 'stripe_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('stripe_id')->nullable()->index();
            });
        }

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            $table->string('event_id')->nullable()->comment('[data][payload][id]');
            $table->string('charge_id')->nullable()->comment('[data][object][id]');
            $table->string('transaction_id')->unique()->comment('[data][object][balance_transaction]');

            $table->string('invoice_id')->nullable()->comment('[data][object][invoice]');
            $table->foreign('invoice_id')->references('invoice_id')->on('invoices')->onDelete('set null')->cascadeOnUpdate();

            $table->string('customer_id')->nullable()->comment('[data][object][customer]');
            $table->foreign('customer_id')->references('stripe_id')->on('users')->onDelete('set null')->cascadeOnUpdate();

            $table->string('payment_method_id')->nullable()->comment('[data][object][payment_method]');
            $table->foreign('payment_method_id')->references('payment_method_id')->on('payment_methods')->onDelete('set null')->cascadeOnUpdate();

            $table->unsignedBigInteger('amount')->comment('[data][object][amount]');
            $table->timestamp('transaction_date')->nullable()->comment('[data][object][created]');
            $table->string('paid')->nullable()->comment('[data][object][paid]');

            $table->string('payment_method_type')->nullable()->comment('[data][object][payment_method_details][type]');
            $table->string('payment_method_details_card_brand')->nullable()->comment('[data][object][payment_method_details][card][brand]');
            $table->string('payment_method_details_card_last4')->nullable()->comment('[data][object][payment_method_details][card][last4]');
            $table->string('payment_method_details_card_exp_month')->nullable()->comment('[data][object][payment_method_details][card][exp_month]');
            $table->string('payment_method_details_card_exp_year')->nullable()->comment('[data][object][payment_method_details][card][exp_year]');
            $table->string('payment_method_details_authorization_code')->nullable()->comment('[data][object][payment_method_details][card][authorization_code]');

            $table->string('receipt_url')->nullable()->comment('[data][object][receipt_url]');
            $table->string('status')->nullable()->comment('[data][object][status]');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
