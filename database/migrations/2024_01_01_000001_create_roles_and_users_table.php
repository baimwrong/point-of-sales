<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create roles, users, sessions, password_reset_tokens, cache tables.
 *
 * This single migration replaces the default Laravel scaffolding migrations and
 * establishes the authentication foundation for the POS system.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ---------------------------------------------------------------
        // roles
        // ---------------------------------------------------------------
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();          // admin | manager | cashier
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();   // optional fine-grained ACL
            $table->timestamps();
        });

        // ---------------------------------------------------------------
        // users
        // ---------------------------------------------------------------
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')
                  ->default(2)
                  ->constrained('roles')
                  ->restrictOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // ---------------------------------------------------------------
        // password_reset_tokens
        // ---------------------------------------------------------------
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // ---------------------------------------------------------------
        // sessions
        // ---------------------------------------------------------------
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // ---------------------------------------------------------------
        // cache
        // ---------------------------------------------------------------
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
};
