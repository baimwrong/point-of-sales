<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create activity_logs table.
 *
 * Provides a comprehensive audit trail of user actions within the POS system,
 * supporting compliance, debugging, and security monitoring.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->string('action');                        // e.g. 'created', 'updated', 'deleted'
            $table->string('model_type')->nullable();        // e.g. 'App\Models\Product'
            $table->unsignedBigInteger('model_id')->nullable(); // PK of the affected record
            $table->text('description');                     // human-readable description
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Index for quick lookups by model type + id
            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
