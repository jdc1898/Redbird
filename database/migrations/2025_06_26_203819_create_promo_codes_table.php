<?php

use Fullstack\Redbird\Models\Discount;
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
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('promo_id')->nullable()->unique();
            $table->boolean('active')->default(true);
            $table->string('code');
            $table->foreignIdFor(Discount::class)
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->timestamp('expires_at')->nullable();
            $table->integer('max_redemptions')->nullable();
            $table->json('metadata')->nullable();
            $table->json('restrictions')->nullable();
            $table->unsignedBigInteger('times_redeemed')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
