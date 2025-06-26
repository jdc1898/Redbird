<?php

use App\Models\Plan;
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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('coupon_id')->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed'])->default('fixed');
            $table->unsignedInteger('amount')->default(0);
            $table->timestamp('valid_until')->nullable();
            $table->integer('max_redemptions')->default(0);
            $table->integer('max_redemptions_per_user')->default(0);
            $table->boolean('is_recurring')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('has_promo_codes')->default(false);
            $table->unsignedInteger('duration_in_months')->default(0);
            $table->integer('maximum_recurring_intervals')->default(0);
            $table->json('promo_codes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
