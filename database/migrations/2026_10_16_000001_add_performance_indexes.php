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
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'sales_status_created_at_idx');
            $table->index('payment_method', 'sales_payment_method_idx');
            $table->index('created_at', 'sales_created_at_idx');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'purchases_status_created_at_idx');
            $table->index('received_at', 'purchases_received_at_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_active', 'track_stock'], 'products_is_active_track_stock_idx');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->index('phone', 'customers_phone_idx');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['action', 'created_at'], 'activity_logs_action_created_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_status_created_at_idx');
            $table->dropIndex('sales_payment_method_idx');
            $table->dropIndex('sales_created_at_idx');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropIndex('purchases_status_created_at_idx');
            $table->dropIndex('purchases_received_at_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_is_active_track_stock_idx');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('customers_phone_idx');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_action_created_at_idx');
        });
    }
};
