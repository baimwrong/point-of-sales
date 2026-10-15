<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\ActivityLog;
use App\Helpers\PosHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'user'])->latest();
        if ($s = $request->get('search')) {
            $query->where(fn($q) => $q->where('reference_number', 'like', "%$s%")
                ->orWhereHas('supplier', fn($sq) => $sq->where('name', 'like', "%$s%")));
        }
        if ($st = $request->get('status')) $query->where('status', $st);
        if ($from = $request->get('date_from')) $query->whereDate('created_at', '>=', $from);
        if ($to   = $request->get('date_to'))   $query->whereDate('created_at', '<=', $to);

        $purchases = $query->paginate(15)->withQueryString();
        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products  = Product::active()->with('unit')->orderBy('name')->get();
        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'       => ['nullable', 'exists:suppliers,id'],
            'status'            => ['required', 'in:pending,received,partial,cancelled'],
            'notes'             => ['nullable', 'string'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.product_id'=> ['required', 'exists:products,id'],
            'items.*.quantity'  => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::beginTransaction();

            $refNum   = PosHelper::generatePurchaseNumber();
            $subtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product    = Product::findOrFail($item['product_id']);
                $totalCost  = (float)$item['unit_cost'] * (int)$item['quantity'];
                $subtotal  += $totalCost;
                $itemsData[] = [
                    'product_id'   => $product->id,
                    'product_name' => $product->name,
                    'product_sku'  => $product->sku,
                    'quantity'     => $item['quantity'],
                    'unit_cost'    => $item['unit_cost'],
                    'total_cost'   => $totalCost,
                ];
            }

            $purchase = Purchase::create([
                'reference_number' => $refNum,
                'supplier_id'      => $request->supplier_id,
                'user_id'          => auth()->id(),
                'status'           => $request->status,
                'subtotal'         => $subtotal,
                'discount_amount'  => 0,
                'tax_amount'       => 0,
                'total_amount'     => $subtotal,
                'paid_amount'      => $request->paid_amount ?? 0,
                'notes'            => $request->notes,
            ]);

            foreach ($itemsData as $item) {
                $purchase->purchaseItems()->create($item);
                // Update stock if directly received and stock is tracked
                if ($request->status === 'received') {
                    Product::where('id', $item['product_id'])->where('track_stock', true)->increment('stock_quantity', $item['quantity']);
                }
            }

            if ($request->status === 'received') {
                $purchase->update(['received_at' => now()]);
            }

            ActivityLog::log('create', "Created purchase order: {$refNum}", $purchase);
            DB::commit();

            return redirect()->route('purchases.show', $purchase->id)
                ->with('success', "Purchase order {$refNum} created successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create purchase: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'user', 'purchaseItems.product'])->findOrFail($id);
        return view('purchases.show', compact('purchase'));
    }

    public function edit($id)
    {
        $purchase  = Purchase::with('purchaseItems.product')->findOrFail($id);
        if ($purchase->status !== 'pending') {
            return back()->with('error', 'Only pending purchases can be edited.');
        }
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products  = Product::active()->with('unit')->orderBy('name')->get();
        return view('purchases.edit', compact('purchase', 'suppliers', 'products'));
    }

    public function update(Request $request, $id)
    {
        $purchase = Purchase::with('purchaseItems')->findOrFail($id);
        $request->validate([
            'notes'  => ['nullable', 'string'],
            'status' => ['required', 'in:pending,received,partial,cancelled'],
        ]);

        $oldStatus = $purchase->status;
        $newStatus = $request->status;

        DB::transaction(function () use ($purchase, $request, $oldStatus, $newStatus) {
            // If changing TO received (and wasn't received before)
            if ($newStatus === 'received' && $oldStatus !== 'received') {
                foreach ($purchase->purchaseItems as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)->where('track_stock', true)->increment('stock_quantity', $item->quantity);
                    }
                }
                $purchase->received_at = now();
            }

            // If changing FROM received to cancelled or pending
            if ($oldStatus === 'received' && $newStatus !== 'received') {
                foreach ($purchase->purchaseItems as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)->where('track_stock', true)->decrement('stock_quantity', $item->quantity);
                    }
                }
                $purchase->received_at = null;
            }

            $purchase->update(['notes' => $request->notes, 'status' => $newStatus]);
        });

        ActivityLog::log('update', "Updated purchase: {$purchase->reference_number}", $purchase);
        return redirect()->route('purchases.show', $purchase->id)->with('success', 'Purchase updated.');
    }

    public function receive($id)
    {
        $purchase = Purchase::with('purchaseItems')->findOrFail($id);
        if ($purchase->status === 'received') {
            return back()->with('error', 'Purchase already marked as received.');
        }

        DB::transaction(function () use ($purchase) {
            foreach ($purchase->purchaseItems as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)->where('track_stock', true)->increment('stock_quantity', $item->quantity);
                }
            }
            $purchase->update(['status' => 'received', 'received_at' => now()]);
            ActivityLog::log('receive', "Received purchase: {$purchase->reference_number}", $purchase);
        });

        return redirect()->route('purchases.show', $purchase->id)->with('success', 'Purchase marked as received and stock updated.');
    }

    public function destroy($id)
    {
        $purchase = Purchase::with('purchaseItems')->findOrFail($id);
        $ref = $purchase->reference_number;

        DB::transaction(function () use ($purchase) {
            if ($purchase->status === 'received') {
                foreach ($purchase->purchaseItems as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)->where('track_stock', true)->decrement('stock_quantity', $item->quantity);
                    }
                }
            }
            $purchase->delete();
        });

        ActivityLog::log('delete', "Deleted purchase: {$ref}");
        return redirect()->route('purchases.index')->with('success', "Purchase {$ref} deleted.");
    }
}
