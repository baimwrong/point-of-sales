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
    protected User $manager;
    protected User $admin;
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

        $managerRole = Role::where('name', 'manager')->first();
        $this->manager = User::factory()->create([
            'role_id' => $managerRole->id,
            'is_active' => true,
        ]);

        $adminRole = Role::where('name', 'admin')->first();
        $this->admin = User::factory()->create([
            'role_id' => $adminRole->id,
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

    public function test_cashier_cannot_process_returns(): void
    {
        $response = $this->actingAs($this->cashier)->post(route('returns.store'), [
            'sale_id' => $this->sale->id,
            'reason' => 'defective',
            'refund_method' => 'cash',
            'items' => [
                [
                    'sale_item_id' => $this->saleItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(403);
    }

    public function test_return_caps_at_sold_quantity_and_restores_stock(): void
    {
        // Initial stock is 10
        // Manager attempts to return 10 items for a sale where only 2 were purchased
        $response = $this->actingAs($this->manager)->post(route('returns.store'), [
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
        $this->actingAs($this->manager)->post(route('returns.store'), [
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
        $response = $this->actingAs($this->manager)->post(route('returns.store'), [
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

    public function test_discounted_item_refund_calculates_against_effective_price(): void
    {
        // Item with original unit_price 25,000, discounted so total is 40,000 for 2 items (effective unit price 20,000)
        $discountedSale = Sale::create([
            'invoice_number' => 'INV-20261016-0002',
            'user_id' => $this->cashier->id,
            'status' => 'completed',
            'payment_method' => 'cash',
            'subtotal' => 50000,
            'total_amount' => 40000,
            'paid_amount' => 40000,
            'change_amount' => 0,
        ]);

        $discountedItem = SaleItem::create([
            'sale_id' => $discountedSale->id,
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'product_sku' => $this->product->sku,
            'quantity' => 2,
            'unit_price' => 25000,
            'total_price' => 40000, // 20,000 each effective
        ]);

        $response = $this->actingAs($this->manager)->post(route('returns.store'), [
            'sale_id' => $discountedSale->id,
            'reason' => 'defective',
            'refund_method' => 'cash',
            'items' => [
                [
                    'sale_item_id' => $discountedItem->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertSessionHas('success');
        $return = SaleReturn::latest('id')->first();
        // Effective refund for 1 item should be 20,000, NOT 25,000
        $this->assertEquals(20000, (float)$return->total_refund);
    }

    public function test_return_deletion_rolls_back_restored_stock_and_recalculates_sale_status(): void
    {
        // Process a return for 2 items
        $this->actingAs($this->manager)->post(route('returns.store'), [
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

        $return = SaleReturn::first();
        $this->assertEquals('refunded', $this->sale->fresh()->status);
        $this->assertEquals(12, $this->product->fresh()->stock_quantity);

        // Admin deletes the return
        $deleteResponse = $this->actingAs($this->admin)->delete(route('returns.destroy', $return->id));
        $deleteResponse->assertSessionHas('success');

        // Stock restored from return should be reverted (decremented from 12 back to 10)
        $this->assertEquals(10, $this->product->fresh()->stock_quantity);

        // Sale status should revert from refunded back to completed
        $this->assertEquals('completed', $this->sale->fresh()->status);

        // Return should be soft deleted
        $this->assertSoftDeleted('sale_returns', ['id' => $return->id]);
    }
}
