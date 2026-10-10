<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Create sales tables.
 *
 * Creates sales, sale_items, and sale_payments tables that represent completed
 * transactions processed through the POS terminal.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ---------------------------------------------------------------
        // sales – the main sales/invoice record
        // ---------------------------------------------------------------
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('customer_id')
                  ->nullable()
                  ->constrained('customers')
                  ->nullOnDelete();
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->enum('status', ['completed', 'refunded', 'partial_refund'])
                  ->default('completed');
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'qris', 'split'])
                  ->default('cash');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->enum('discount_type', ['none', 'fixed', 'percent'])->default('none');
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ---------------------------------------------------------------
        // sale_items – individual line items within a sale
        // ---------------------------------------------------------------
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')
                  ->constrained('sales')
                  ->cascadeOnDelete();
            $table->foreignId('product_id')
                  ->nullable()
                  ->constrained('products')
                  ->nullOnDelete();
            $table->string('product_name');   // snapshot at time of sale
            $table->string('product_sku');    // snapshot at time of sale
            $table->string('barcode')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->enum('discount_type', ['none', 'fixed', 'percent'])->default('none');
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2);
            $table->timestamps();
        });

        // ---------------------------------------------------------------
        // sale_payments – multi-tender payment records for split payments
        // ---------------------------------------------------------------
        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')
                  ->constrained('sales')
                  ->cascadeOnDelete();
            $table->string('payment_method');
            $table->decimal('amount', 15, 2);
            $table->string('reference')->nullable(); // card/transfer ref number
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
