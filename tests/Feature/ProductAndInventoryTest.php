<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductAndInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $adminRole = Role::where('name', 'admin')->first();
        $this->admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $managerRole = Role::where('name', 'manager')->first();
        $this->manager = User::factory()->create([
            'role_id' => $managerRole->id,
            'is_active' => true,
        ]);
    }

    public function test_product_store_and_update_persists_wholesale_price_and_discount(): void
    {
        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'name' => 'Premium Arabica Coffee',
            'sku' => 'COF-PREM-001',
            'barcode' => '899999999001',
            'selling_price' => 50000,
            'cost_price' => 30000,
            'wholesale_price' => 45000,
            'discount_type' => 'percent',
            'discount_value' => 15,
            'stock_quantity' => 100,
            'min_stock_alert' => 10,
            'is_active' => 1,
            'track_stock' => 1,
        ]);

        $response->assertSessionHas('success');

        $product = Product::where('sku', 'COF-PREM-001')->first();
        $this->assertNotNull($product);
        $this->assertEquals(45000, (float)$product->wholesale_price);
        $this->assertEquals('percent', $product->discount_type);
        $this->assertEquals(15, (float)$product->discount_value);

        // Update product
        $updateResponse = $this->actingAs($this->admin)->put(route('products.update', $product->id), [
            'name' => 'Premium Arabica Coffee - Updated',
            'sku' => 'COF-PREM-001',
            'selling_price' => 55000,
            'cost_price' => 32000,
            'wholesale_price' => 48000,
            'discount_type' => 'fixed',
            'discount_value' => 5000,
            'stock_quantity' => 90,
            'is_active' => 1,
            'track_stock' => 1,
        ]);

        $updateResponse->assertSessionHas('success');
        $product->refresh();
        $this->assertEquals(48000, (float)$product->wholesale_price);
        $this->assertEquals('fixed', $product->discount_type);
        $this->assertEquals(5000, (float)$product->discount_value);
    }

    public function test_product_store_rejects_discount_percentage_over_100(): void
    {
        $response = $this->actingAs($this->admin)->post(route('products.store'), [
            'name' => 'Invalid Discount Product',
            'sku' => 'INV-DISC-001',
            'selling_price' => 50000,
            'cost_price' => 30000,
            'discount_type' => 'percent',
            'discount_value' => 110, // > 100%
            'stock_quantity' => 10,
        ]);

        $response->assertSessionHasErrors(['discount_value']);
    }

    public function test_category_update_rejects_circular_parent_id(): void
    {
        $category = Category::create([
            'name' => 'Beverages',
            'slug' => 'beverages',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('categories.update', $category->id), [
            'name' => 'Beverages Updated',
            'parent_id' => $category->id, // Cannot be its own parent
        ]);

        $response->assertSessionHasErrors(['parent_id']);
    }

    public function test_purchase_receive_does_not_increment_non_tracked_products(): void
    {
        $supplier = Supplier::create([
            'name' => 'General Supplier',
            'is_active' => true,
        ]);

        $serviceProduct = Product::create([
            'name' => 'Repair Service',
            'slug' => 'repair-service',
            'sku' => 'SRV-001',
            'selling_price' => 100000,
            'cost_price' => 50000,
            'stock_quantity' => 0,
            'is_active' => true,
            'track_stock' => false,
        ]);

        $purchase = Purchase::create([
            'reference_number' => 'PO-20261016-0001',
            'supplier_id' => $supplier->id,
            'user_id' => $this->admin->id,
            'status' => 'pending',
            'total_amount' => 100000,
            'paid_amount' => 100000,
            'payment_status' => 'paid',
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $serviceProduct->id,
            'product_name' => $serviceProduct->name,
            'product_sku' => $serviceProduct->sku,
            'quantity' => 10,
            'unit_cost' => 10000,
            'total_cost' => 100000,
        ]);

        // Receive the purchase
        $this->actingAs($this->admin)->put(route('purchases.update', $purchase->id), [
            'status' => 'received',
        ]);

        // Stock must remain 0 because track_stock is false
        $this->assertEquals(0, $serviceProduct->fresh()->stock_quantity);
    }

    public function test_import_restores_soft_deleted_sku_when_update_existing_checked(): void
    {
        $product = Product::create([
            'name' => 'Archived Item',
            'slug' => 'archived-item',
            'sku' => 'SKU-IMPORT-001',
            'selling_price' => 30000,
            'cost_price' => 20000,
            'stock_quantity' => 5,
            'is_active' => true,
        ]);

        // Soft delete the product
        $product->delete();
        $this->assertSoftDeleted('products', ['id' => $product->id]);

        // Prepare CSV with same SKU
        $csvContent = "Name,SKU,Barcode,Category,Unit,Cost Price,Selling Price,Wholesale Price,Stock Quantity,Min Stock Alert,Description\n"
                    . "Restored Item,SKU-IMPORT-001,123456789,General,pcs,22000,35000,32000,50,5,Restored from import\n";

        $file = UploadedFile::fake()->createWithContent('products.csv', $csvContent);

        $response = $this->actingAs($this->admin)->post(route('products.import.process'), [
            'file' => $file,
            'update_existing' => 1,
        ]);

        $response->assertSessionHas('success');

        $refreshed = Product::withTrashed()->where('sku', 'SKU-IMPORT-001')->first();
        $this->assertNotNull($refreshed);
        $this->assertFalse($refreshed->trashed()); // Restored
        $this->assertEquals('Restored Item', $refreshed->name);
        $this->assertEquals(35000, (float)$refreshed->selling_price);
        $this->assertEquals(50, $refreshed->stock_quantity);
    }

    public function test_setting_cache_invalidation_and_retrieval(): void
    {
        Setting::clearCache();

        Setting::set('app_test_key', 'initial_value');
        $this->assertEquals('initial_value', Setting::get('app_test_key'));

        Setting::set('app_test_key', 'updated_value');
        $this->assertEquals('updated_value', Setting::get('app_test_key'));

        Setting::clearCache();
        $this->assertEquals('updated_value', Setting::get('app_test_key'));
    }
}
