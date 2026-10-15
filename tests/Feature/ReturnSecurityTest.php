<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Product $product;
    protected Sale $sale;
    protected SaleItem $saleItem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $cashierRole = Role::where('name', 'cashier')->first();
        $this->cashier = User::factory()->create([
            'role_id' => $cashierRole->id,
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'name' => 'Widget A',
            'slug' => 'widget-a',
            'sku' => 'SKU-WIDGET-A',
            'selling_price' => 25000,
            'cost_price' => 15000,
            'stock_quantity' => 10,
            'is_active' => true,
            'track_stock' => true,
        ]);

        $this->sale = Sale::create([
            'invoice_number' => 'INV-20261016-0001',
            'user_id' => $this->cashier->id,
            'status' => 'completed',
            'payment_method' => 'cash',
            'subtotal' => 50000,
            'total_amount' => 50000,
            'paid_amount' => 50000,
            'change_amount' => 0,
        ]);

        $this->saleItem = SaleItem::create([
            'sale_id' => $this->sale->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku' => $this->product->sku,
            'quantity' => 2,
            'unit_price' => 25000,
            'total_price' => 50000,
        ]);
    }

    public function test_return_caps_at_sold_quantity_and_restores_stock(): void
    {
        // Initial stock is 10
        // Client attempts to return 10 items for a sale where only 2 were purchased
        $response = $this->actingAs($this->cashier)->post(route('returns.store'), [
            'sale_id' => $this->sale->id,
            'reason' => 'defective',
            'refund_method' => 'cash',
            'items' => [
                [
                    'sale_item_id' => $this->saleItem->id,
                    'quantity' => 10, // Attempting to return 10
                ],
            ],
        ]);

        $response->assertSessionHas('success');

        // Only 2 should have been returned
        $return = SaleReturn::first();
        $this->assertNotNull($return);
        $this->assertEquals(50000, (float)$return->total_refund);

        $returnItem = SaleReturnItem::first();
        $this->assertEquals(2, $returnItem->quantity);

        // Stock restored by exactly 2 (10 + 2 = 12)
        $this->assertEquals(12, $this->product->fresh()->stock_quantity);

        // Sale status updated to refunded
        $this->assertEquals('refunded', $this->sale->fresh()->status);
    }

    public function test_cannot_return_more_after_full_refund(): void
    {
        // First return returns 2 items
        $this->actingAs($this->cashier)->post(route('returns.store'), [
            'sale_id' => $this->sale->id,
            'reason' => 'defective',
            'refund_method' => 'cash',
            'items' => [
                [
                    'sale_item_id' => $this->saleItem->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        // Second attempt to return again for the same sale item
        $response = $this->actingAs($this->cashier)->post(route('returns.store'), [
            'sale_id' => $this->sale->id,
            'reason' => 'customer_request',
            'refund_method' => 'cash',
            'items' => [
                [
                    'sale_item_id' => $this->saleItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertSessionHas('error');
        // Only 1 return record should exist
        $this->assertEquals(1, SaleReturn::count());
    }
}
