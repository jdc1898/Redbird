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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('active')->default(false);
            $table->string('default_price')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->string('tax_code');
            $table->json('images')->nullable();
            $table->json('marketing_features')->nullable();
            $table->json('package_dimensions')->nullable();
            $table->boolean('shippable')->default(false);
            $table->string('statement_descriptor', 22)->nullable();
            $table->string('unit_label')->nullable();
            $table->string('url')->nullable();
            $table->string('product_id')->nullable()->unique();
            $table->string('slug');
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
        Schema::dropIfExists('products');
    }
};
