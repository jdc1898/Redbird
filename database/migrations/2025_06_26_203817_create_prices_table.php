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
        Schema::create('prices', function (Blueprint $table) {
            $table->id();
            $table->boolean('active')->default(false);
            $table->string('currency')->default('usd');
            $table->json('metadata')->nullable();
            $table->string('nickname')->nullable();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->json('recurring')->nullable();
            $table->enum('tax_behavior', ['inclusive', 'exclusive', 'unspecified'])->nullable();
            $table->enum('type', ['one_time', 'recurring'])->nullable();
            $table->unsignedInteger('unit_amount')->nullable();
            $table->string('object')->nullable();
            $table->enum('billing_scheme', ['per_unit', 'tiered'])->nullable();
            $table->json('currency_options')->nullable();
            $table->json('custom_unit_amount')->nullable();
            $table->boolean('livemode')->default(false);
            $table->string('lookup_key')->nullable();
            $table->json('tiers')->nullable();
            $table->enum('tiers_mode', ['graduated', 'volume'])->nullable();
            $table->json('transform_quantity')->nullable();
            $table->decimal('unit_amount_decimal', 10, 2)->nullable();
            $table->string('price_id')->nullable()->unique();
            $table->boolean('is_synced')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prices');
    }
};
