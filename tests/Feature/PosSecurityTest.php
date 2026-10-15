<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Product $product;

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
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'SKU-TEST-001',
            'selling_price' => 50000,
            'cost_price' => 30000,
            'stock_quantity' => 10,
            'is_active' => true,
            'track_stock' => true,
        ]);
    }

    public function test_client_cannot_manipulate_pos_item_price(): void
    {
        $response = $this->actingAs($this->cashier)->postJson(route('pos.transaction'), [
            'items' => [
                [
                    'id' => $this->product->id,
                    'qty' => 2,
                    'price' => 100, // Attacker tries to spoof price to Rp 100
                ],
            ],
            'payment_method' => 'cash',
            'paid_amount' => 120000,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $sale = Sale::first();
        $this->assertNotNull($sale);
        // Subtotal must be calculated from database (50,000 * 2 = 100,000), NOT spoofed price (100 * 2 = 200)
        $this->assertEquals(100000, (float)$sale->subtotal);

        $saleItem = $sale->items->first();
        $this->assertEquals(50000, (float)$saleItem->unit_price);
        $this->assertEquals(100000, (float)$saleItem->total_price);
        $this->assertEquals(8, $this->product->fresh()->stock_quantity);
    }

    public function test_split_payment_rejects_insufficient_total(): void
    {
        $response = $this->actingAs($this->cashier)->postJson(route('pos.transaction'), [
            'items' => [
                [
                    'id' => $this->product->id,
                    'qty' => 1,
                ],
            ],
            'payment_method' => 'split',
            'paid_amount' => 50000,
            'split_payments' => [
                ['method' => 'cash', 'amount' => 20000], // total 20,000 is less than 50,000
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_cannot_sell_more_than_available_stock(): void
    {
        $response = $this->actingAs($this->cashier)->postJson(route('pos.transaction'), [
            'items' => [
                [
                    'id' => $this->product->id,
                    'qty' => 999, // only 10 in stock
                ],
            ],
            'payment_method' => 'cash',
            'paid_amount' => 50000000,
        ]);

        $response->assertStatus(422);
    }

    public function test_order_discount_percentage_over_100_fails_validation(): void
    {
        $response = $this->actingAs($this->cashier)->postJson(route('pos.transaction'), [
            'items' => [
                [
                    'id' => $this->product->id,
                    'qty' => 1,
                ],
            ],
            'payment_method' => 'cash',
            'paid_amount' => 50000,
            'discount_type' => 'percent',
            'discount_value' => 105, // > 100%
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['discount_value']);
    }

    public function test_item_discount_percentage_over_100_fails_validation(): void
    {
        $response = $this->actingAs($this->cashier)->postJson(route('pos.transaction'), [
            'items' => [
                [
                    'id' => $this->product->id,
                    'qty' => 1,
                    'discount_type' => 'percent',
                    'discount_value' => 120, // > 100%
                ],
            ],
            'payment_method' => 'cash',
            'paid_amount' => 50000,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['items.0.discount_value']);
    }

    public function test_item_discount_overrides_catalog_discount_without_compounding(): void
    {
        // Set catalog discount to 10%
        $this->product->update([
            'discount_type' => 'percent',
            'discount_value' => 10,
        ]);

        // Cashier overrides line discount with fixed 10,000 off
        // Selling price = 50,000, so line total should be 40,000 (NOT compounded with 10%)
        $response = $this->actingAs($this->cashier)->postJson(route('pos.transaction'), [
            'items' => [
                [
                    'id' => $this->product->id,
                    'qty' => 1,
                    'discount_type' => 'fixed',
                    'discount_value' => 10000,
                ],
            ],
            'payment_method' => 'cash',
            'paid_amount' => 50000,
        ]);

        $response->assertStatus(200);
        $sale = Sale::first();
        $this->assertNotNull($sale);
        $this->assertEquals(40000, (float)$sale->subtotal);
    }
}
