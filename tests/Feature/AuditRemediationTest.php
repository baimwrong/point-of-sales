<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditRemediationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $cashier;
    protected Role $adminRole;
    protected Role $cashierRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->adminRole = Role::where('name', 'admin')->first();
        $this->cashierRole = Role::where('name', 'cashier')->first();

        $this->admin = User::factory()->create([
            'role_id'   => $this->adminRole->id,
            'is_active' => true,
        ]);

        $this->cashier = User::factory()->create([
            'role_id'   => $this->cashierRole->id,
            'is_active' => true,
        ]);
    }

    public function test_category_route_binding_uses_numeric_id(): void
    {
        $category = Category::create([
            'name'       => 'Electronics',
            'slug'       => 'electronics',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)->get(route('categories.edit', $category->id));
        $response->assertStatus(200);
        $response->assertSee('Electronics');
    }

    public function test_supplier_request_persists_city_country_and_is_active(): void
    {
        $response = $this->actingAs($this->admin)->post(route('suppliers.store'), [
            'name'        => 'Global Tech Supplies',
            'company'     => 'Global Tech Inc',
            'email'       => 'orders@globaltech.com',
            'phone'       => '08123456789',
            'address'     => 'Jl. Sudirman No 1',
            'city'        => 'Jakarta',
            'country'     => 'Indonesia',
            'tax_number'  => 'NPWP-999-888',
            'is_active'   => 1,
        ]);

        $response->assertRedirect(route('suppliers.index'));

        $supplier = Supplier::where('email', 'orders@globaltech.com')->first();
        $this->assertNotNull($supplier);
        $this->assertEquals('Jakarta', $supplier->city);
        $this->assertEquals('Indonesia', $supplier->country);
        $this->assertTrue((bool)$supplier->is_active);
    }

    public function test_pos_transaction_groups_duplicate_product_items_to_prevent_overselling(): void
    {
        $product = Product::create([
            'name'           => 'Limited Stock Item',
            'slug'           => 'limited-stock-item',
            'sku'            => 'SKU-LTD-001',
            'selling_price'  => 50000,
            'cost_price'     => 30000,
            'stock_quantity' => 5, // Only 5 in stock
            'is_active'      => true,
            'track_stock'    => true,
        ]);

        // Attacker splits request into two lines of 3 items (total 6 > 5)
        $response = $this->actingAs($this->cashier)->postJson(route('pos.transaction'), [
            'items' => [
                ['id' => $product->id, 'qty' => 3, 'price' => 50000],
                ['id' => $product->id, 'qty' => 3, 'price' => 50000],
            ],
            'payment_method' => 'cash',
            'paid_amount'    => 300000,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Insufficient stock for "Limited Stock Item". Available: 5',
        ]);

        // Stock remains untouched
        $this->assertEquals(5, $product->fresh()->stock_quantity);
    }

    public function test_return_controller_filters_out_zero_quantity_items(): void
    {
        $product1 = Product::create([
            'name'           => 'Returnable Prod 1',
            'slug'           => 'ret-prod-1',
            'sku'            => 'SKU-RET-001',
            'selling_price'  => 50000,
            'cost_price'     => 30000,
            'stock_quantity' => 10,
            'is_active'      => true,
            'track_stock'    => true,
        ]);

        $product2 = Product::create([
            'name'           => 'Returnable Prod 2',
            'slug'           => 'ret-prod-2',
            'sku'            => 'SKU-RET-002',
            'selling_price'  => 25000,
            'cost_price'     => 15000,
            'stock_quantity' => 10,
            'is_active'      => true,
            'track_stock'    => true,
        ]);

        $sale = Sale::create([
            'invoice_number' => 'INV-20261016-0001',
            'user_id'        => $this->admin->id,
            'status'         => 'completed',
            'payment_method' => 'cash',
            'subtotal'       => 75000,
            'discount_type'  => 'fixed',
            'discount_amount'=> 0,
            'tax_rate'       => 0,
            'tax_amount'     => 0,
            'total_amount'   => 75000,
            'paid_amount'    => 75000,
            'change_amount'  => 0,
        ]);

        $item1 = SaleItem::create([
            'sale_id'         => $sale->id,
            'product_id'      => $product1->id,
            'product_name'    => $product1->name,
            'product_sku'     => $product1->sku,
            'quantity'        => 1,
            'unit_price'      => 50000,
            'discount_type'   => 'fixed',
            'discount_amount' => 0,
            'total_price'     => 50000,
        ]);

        $item2 = SaleItem::create([
            'sale_id'         => $sale->id,
            'product_id'      => $product2->id,
            'product_name'    => $product2->name,
            'product_sku'     => $product2->sku,
            'quantity'        => 1,
            'unit_price'      => 25000,
            'discount_type'   => 'fixed',
            'discount_amount' => 0,
            'total_price'     => 25000,
        ]);

        // Submit return where product 1 has quantity 1, but product 2 has quantity 0 (not being returned)
        $response = $this->actingAs($this->admin)->post(route('returns.store'), [
            'sale_id'       => $sale->id,
            'refund_method' => 'cash',
            'reason'        => 'Customer changed mind on item 1',
            'items'         => [
                [
                    'sale_item_id' => $item1->id,
                    'quantity'     => 1,
                    'unit_price'   => 50000,
                ],
                [
                    'sale_item_id' => $item2->id,
                    'quantity'     => 0,
                    'unit_price'   => 25000,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('sale_returns', [
            'sale_id'      => $sale->id,
            'total_refund' => 50000,
        ]);
        $this->assertEquals(11, $product1->fresh()->stock_quantity);
        $this->assertEquals(10, $product2->fresh()->stock_quantity);
    }

    public function test_admin_cannot_demote_or_deactivate_self(): void
    {
        $response = $this->actingAs($this->admin)->put(route('users.update', $this->admin->id), [
            'name'      => $this->admin->name,
            'email'     => $this->admin->email,
            'role_id'   => $this->cashierRole->id, // Trying to demote self
            'is_active' => 1,
        ]);

        $response->assertSessionHas('error', 'You cannot change your own role.');
        $this->assertEquals($this->adminRole->id, $this->admin->fresh()->role_id);

        $response2 = $this->actingAs($this->admin)->put(route('users.update', $this->admin->id), [
            'name'      => $this->admin->name,
            'email'     => $this->admin->email,
            'role_id'   => $this->adminRole->id,
            'is_active' => 0, // Trying to deactivate self
        ]);

        $response2->assertSessionHas('error', 'You cannot deactivate your own account.');
        $this->assertTrue((bool)$this->admin->fresh()->is_active);
    }

    public function test_cannot_deactivate_or_delete_last_active_admin(): void
    {
        // $this->admin is the only admin
        $secondAdmin = User::factory()->create([
            'role_id'   => $this->adminRole->id,
            'is_active' => true,
        ]);

        // Second admin deactivates first admin -> allowed since second admin remains active
        $response = $this->actingAs($secondAdmin)->post(route('users.toggleActive', $this->admin->id));
        $response->assertSessionHas('success');
        $this->assertFalse((bool)$this->admin->fresh()->is_active);

        // Now second admin tries to deactivate self -> blocked by self-check
        $response2 = $this->actingAs($secondAdmin)->post(route('users.toggleActive', $secondAdmin->id));
        $response2->assertSessionHas('error', 'You cannot deactivate your own account.');

        // Another user or API attempting to demote or deactivate the last active admin
        $response3 = $this->actingAs($secondAdmin)->put(route('users.update', $secondAdmin->id), [
            'name'      => $secondAdmin->name,
            'email'     => $secondAdmin->email,
            'role_id'   => $this->cashierRole->id,
            'is_active' => 1,
        ]);
        $response3->assertSessionHas('error', 'You cannot change your own role.');
    }

    public function test_purchase_unpaid_amount_updates_supplier_balance(): void
    {
        $supplier = Supplier::create([
            'name'      => 'Wholesale Distributor',
            'company'   => 'Wholesale Co',
            'balance'   => 0,
            'is_active' => true,
        ]);

        $product = Product::create([
            'name'           => 'Stock Inventory Item',
            'slug'           => 'stock-inventory-item',
            'sku'            => 'SKU-INV-001',
            'selling_price'  => 50000,
            'cost_price'     => 30000,
            'stock_quantity' => 10,
            'is_active'      => true,
            'track_stock'    => true,
        ]);

        // Purchase total = 10 * 30,000 = 300,000. Paid = 100,000. Unpaid = 200,000.
        $response = $this->actingAs($this->admin)->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'status'      => 'pending',
            'paid_amount' => 100000,
            'notes'       => 'Partial payment purchase order',
            'items'       => [
                [
                    'product_id' => $product->id,
                    'quantity'   => 10,
                    'unit_cost'  => 30000,
                ],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals(200000, (float)$supplier->fresh()->balance);

        $purchase = Purchase::where('supplier_id', $supplier->id)->latest()->first();

        // Cancelling purchase reduces balance back
        $this->actingAs($this->admin)->put(route('purchases.update', $purchase->id), [
            'status' => 'cancelled',
            'notes'  => 'Cancelled order',
        ]);
        $this->assertEquals(0, (float)$supplier->fresh()->balance);

        // Reactivating purchase restores debt
        $this->actingAs($this->admin)->put(route('purchases.update', $purchase->id), [
            'status' => 'pending',
            'notes'  => 'Reinstated order',
        ]);
        $this->assertEquals(200000, (float)$supplier->fresh()->balance);

        // Deleting purchase clears debt
        $this->actingAs($this->admin)->delete(route('purchases.destroy', $purchase->id));
        $this->assertEquals(0, (float)$supplier->fresh()->balance);
    }

    public function test_deactivated_user_is_blocked_from_pos_and_logged_out(): void
    {
        $this->cashier->update(['is_active' => false]);

        $response = $this->actingAs($this->cashier)->get(route('pos.index'));
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_product_report_excludes_soft_deleted_sales_and_products(): void
    {
        $product = Product::create([
            'name'           => 'Reporting Test Item',
            'slug'           => 'rep-test-item',
            'sku'            => 'SKU-REP-001',
            'selling_price'  => 100000,
            'cost_price'     => 60000,
            'stock_quantity' => 20,
            'is_active'      => true,
            'track_stock'    => true,
        ]);

        $sale = Sale::create([
            'invoice_number' => 'INV-20261016-0002',
            'user_id'        => $this->admin->id,
            'status'         => 'completed',
            'payment_method' => 'cash',
            'subtotal'       => 100000,
            'discount_type'  => 'fixed',
            'discount_amount'=> 0,
            'tax_rate'       => 0,
            'tax_amount'     => 0,
            'total_amount'   => 100000,
            'paid_amount'    => 100000,
            'change_amount'  => 0,
        ]);

        SaleItem::create([
            'sale_id'         => $sale->id,
            'product_id'      => $product->id,
            'product_name'    => $product->name,
            'product_sku'     => $product->sku,
            'quantity'        => 2,
            'unit_price'      => 100000,
            'discount_type'   => 'fixed',
            'discount_amount' => 0,
            'total_price'     => 200000,
        ]);

        // Verify it shows up in products report
        $resBefore = $this->actingAs($this->admin)->get(route('reports.products'));
        $resBefore->assertStatus(200);
        $resBefore->assertSee('Reporting Test Item');

        // Soft-delete the sale
        $sale->delete();

        // Product report should now exclude the deleted sale
        $resAfter = $this->actingAs($this->admin)->get(route('reports.products'));
        $resAfter->assertStatus(200);
        $resAfter->assertDontSee('Reporting Test Item');
    }
}
