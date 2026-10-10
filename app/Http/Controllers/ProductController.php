<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\ActivityLog;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'unit'])->latest();

        if ($search = $request->get('search')) {
            $query->search($search);
        }
        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }
        if ($stock = $request->get('stock_status')) {
            if ($stock === 'low')  $query->lowStock();
            if ($stock === 'out')  $query->where('stock_quantity', 0);
            if ($stock === 'in')   $query->where('stock_quantity', '>', 0);
        }
        if ($status = $request->get('status')) {
            $query->where('is_active', $status === 'active');
        }

        $products   = $query->paginate(15)->withQueryString();
        $categories = Category::active()->orderBy('name')->get();
        $lowStockCount = Product::lowStock()->count();

        return view('products.index', compact('products', 'categories', 'lowStockCount'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        $units      = Unit::orderBy('name')->get();
        return view('products.create', compact('categories', 'units'));
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(6);

        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage($request->file('image'));
        }

        $product = Product::create($data);
        ActivityLog::log('create', "Created product: {$product->name}", $product);

        return redirect()->route('products.index')
            ->with('success', "Product \"{$product->name}\" created successfully.");
    }

    public function show($id)
    {
        $product = Product::with(['category', 'unit', 'productImages'])->findOrFail($id);

        $totalSold = $product->saleItems()->sum('quantity');
        $totalRevenue = $product->saleItems()->sum('total_price');

        return view('products.show', compact('product', 'totalSold', 'totalRevenue'));
    }

    public function edit($id)
    {
        $product    = Product::findOrFail($id);
        $categories = Category::active()->orderBy('name')->get();
        $units      = Unit::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories', 'units'));
    }

    public function update(ProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $data    = $request->validated();

        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image && file_exists(public_path('images/products/' . $product->image))) {
                unlink(public_path('images/products/' . $product->image));
            }
            $data['image'] = $this->uploadImage($request->file('image'));
        }

        $product->update($data);
        ActivityLog::log('update', "Updated product: {$product->name}", $product);

        return redirect()->route('products.index')
            ->with('success', "Product \"{$product->name}\" updated successfully.");
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        ActivityLog::log('delete', "Deleted product: {$product->name}", $product);

        return redirect()->route('products.index')
            ->with('success', "Product \"{$product->name}\" deleted successfully.");
    }

    public function search(Request $request)
    {
        $term     = $request->get('q', '');
        $products = Product::active()
            ->search($term)
            ->with('unit')
            ->limit(20)
            ->get()
            ->map(fn($p) => [
                'id'             => $p->id,
                'name'           => $p->name,
                'sku'            => $p->sku,
                'barcode'        => $p->barcode,
                'selling_price'  => (float)$p->selling_price,
                'effective_price'=> (float)$p->effective_price,
                'tax_rate'       => (float)$p->tax_rate,
                'stock_quantity' => $p->stock_quantity,
                'track_stock'    => $p->track_stock,
                'unit'           => $p->unit?->abbreviation ?? 'pcs',
                'image_url'      => $p->image_url,
                'discount_type'  => $p->discount_type,
                'discount_value' => (float)$p->discount_value,
            ]);

        return response()->json($products);
    }

    public function updateStock(Request $request, $id)
    {
        $request->validate([
            'quantity'   => ['required', 'integer'],
            'adjustment' => ['required', 'in:add,subtract,set'],
            'reason'     => ['nullable', 'string', 'max:255'],
        ]);

        $product = Product::findOrFail($id);
        $qty     = (int) $request->quantity;
        $oldQty  = $product->stock_quantity;

        match($request->adjustment) {
            'add'      => $product->increment('stock_quantity', $qty),
            'subtract' => $product->decrement('stock_quantity', min($qty, $product->stock_quantity)),
            'set'      => $product->update(['stock_quantity' => $qty]),
        };

        $product->refresh();
        ActivityLog::log(
            'stock_update',
            "Stock updated for {$product->name}: {$oldQty} → {$product->stock_quantity}" .
            ($request->reason ? " (Reason: {$request->reason})" : ''),
            $product
        );

        return redirect()->back()->with('success', 'Stock updated successfully.');
    }

    private function uploadImage($file): string
    {
        $dir      = public_path('images/products');
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = time() . '_' . Str::random(8) . '.' . $file->extension();
        $file->move($dir, $filename);
        return $filename;
    }
}
