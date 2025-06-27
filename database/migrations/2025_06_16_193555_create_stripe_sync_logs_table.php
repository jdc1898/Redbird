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
        Schema::create('stripe_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('syncable'); // This will create syncable_type and syncable_id columns
            $table->string('action'); // create, update, delete
            $table->string('status'); // pending, success, failed
            $table->text('error_message')->nullable();
            $table->json('error_context')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('succeeded_at')->nullable();
            $table->timestamps();

            // Index for quick lookups of failed syncs
            $table->index(['status', 'attempts']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_sync_logs');
    }
};
