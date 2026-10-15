<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\Customer;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Helpers\PosHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $taxRate    = (float) Setting::get('tax_rate', 11);
        $categories = Category::active()->orderBy('sort_order')->orderBy('name')->get();
        $currency   = Setting::get('currency_symbol', 'Rp');
        $storeName  = Setting::get('store_name', 'POS System');
        return view('pos.index', compact('taxRate', 'categories', 'currency', 'storeName'));
    }

    public function searchProduct(Request $request)
    {
        $term = $request->get('q', '');
        $products = Product::active()
            ->search($term)
            ->with(['category', 'unit'])
            ->limit(30)
            ->get()
            ->map(fn($p) => $this->productPayload($p));

        return response()->json($products);
    }

    public function getProduct($id)
    {
        $product = Product::active()->with(['category', 'unit'])->findOrFail($id);
        return response()->json($this->productPayload($product));
    }

    public function processTransaction(Request $request)
    {
        $request->validate([
            'items'                   => ['required', 'array', 'min:1'],
            'items.*.id'              => ['required', 'exists:products,id'],
            'items.*.qty'             => ['required', 'integer', 'min:1'],
            'items.*.price'           => ['nullable', 'numeric'],
            'items.*.discount_type'   => ['nullable', 'in:none,fixed,percent'],
            'items.*.discount_value'  => ['nullable', 'numeric', 'min:0'],
            'payment_method'          => ['required', 'in:cash,card,transfer,qris,split'],
            'paid_amount'             => ['required', 'numeric', 'min:0'],
            'customer_id'             => ['nullable', 'exists:customers,id'],
            'discount_type'           => ['nullable', 'in:none,fixed,percent'],
            'discount_value'          => ['nullable', 'numeric', 'min:0'],
            'notes'                   => ['nullable', 'string', 'max:500'],
            'split_payments'          => ['required_if:payment_method,split', 'array', 'min:1'],
            'split_payments.*.method' => ['required', 'in:cash,card,transfer,qris'],
            'split_payments.*.amount' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::beginTransaction();

            $items       = $request->items;
            $taxRate     = (float) Setting::get('tax_rate', 11);
            $subtotal    = 0;

            // Batch lock all products to prevent race conditions and N+1 queries
            $productIds = collect($items)->pluck('id')->unique()->toArray();
            $products   = Product::lockForUpdate()->whereIn('id', $productIds)->get()->keyBy('id');

            // Validate stock & compute subtotal using server-side product pricing
            $productMap = [];
            foreach ($items as $item) {
                $product = $products->get($item['id']);
                if (!$product) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Product not found.',
                    ], 422);
                }

                if ($product->track_stock && $product->stock_quantity < $item['qty']) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for \"{$product->name}\". Available: {$product->stock_quantity}",
                    ], 422);
                }

                $unitPrice    = (float) $product->effective_price;
                $rawTotal     = $unitPrice * (int) $item['qty'];
                $itemDiscType = $item['discount_type'] ?? 'none';
                $itemDiscount = 0;

                // Apply item discount
                if (!empty($itemDiscType) && $itemDiscType !== 'none') {
                    $itemDiscount = $itemDiscType === 'percent'
                        ? $rawTotal * ((float)($item['discount_value'] ?? 0) / 100)
                        : (float)($item['discount_value'] ?? 0);
                }
                $lineTotal = max(0, $rawTotal - $itemDiscount);
                $subtotal += $lineTotal;

                $productMap[] = [
                    'product'      => $product,
                    'item'         => $item,
                    'unitPrice'    => $unitPrice,
                    'itemDiscType' => $itemDiscType,
                    'itemDiscount' => $itemDiscount,
                    'lineTotal'    => $lineTotal,
                ];
            }

            // Order-level discount
            $discType   = $request->discount_type ?? 'none';
            $discValue  = (float)($request->discount_value ?? 0);
            $discAmount = 0;
            if ($discType === 'fixed')   $discAmount = min($discValue, $subtotal);
            if ($discType === 'percent') $discAmount = $subtotal * ($discValue / 100);

            $taxableAmount = $subtotal - $discAmount;
            $taxAmount     = round($taxableAmount * ($taxRate / 100), 2);
            $totalAmount   = round($taxableAmount + $taxAmount, 2);
            $paidAmount    = (float)$request->paid_amount;

            if ($request->payment_method === 'split') {
                $splitTotal = (float)collect($request->split_payments)->sum('amount');
                if ($splitTotal < $totalAmount) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Total split payment amount (' . number_format($splitTotal) . ') is less than required total (' . number_format($totalAmount) . ').',
                    ], 422);
                }
                $paidAmount = $splitTotal;
            } elseif ($paidAmount < $totalAmount) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Paid amount is less than total amount due.',
                ], 422);
            }

            $changeAmount  = max(0, $paidAmount - $totalAmount);

            // Create Sale
            $invoiceNumber = PosHelper::generateInvoiceNumber();
            $sale = Sale::create([
                'invoice_number'  => $invoiceNumber,
                'customer_id'     => $request->customer_id,
                'user_id'         => auth()->id(),
                'status'          => 'completed',
                'payment_method'  => $request->payment_method,
                'subtotal'        => $subtotal,
                'discount_type'   => $discType,
                'discount_amount' => $discAmount,
                'tax_rate'        => $taxRate,
                'tax_amount'      => $taxAmount,
                'total_amount'    => $totalAmount,
                'paid_amount'     => $paidAmount,
                'change_amount'   => $changeAmount,
                'notes'           => $request->notes,
            ]);

            // Create sale items & update stock
            foreach ($productMap as $entry) {
                $product      = $entry['product'];
                $item         = $entry['item'];
                $unitPrice    = $entry['unitPrice'];
                $itemDiscType = $entry['itemDiscType'];
                $itemDiscount = $entry['itemDiscount'];
                $lineTotal    = $entry['lineTotal'];

                SaleItem::create([
                    'sale_id'         => $sale->id,
                    'product_id'      => $product->id,
                    'product_name'    => $product->name,
                    'product_sku'     => $product->sku,
                    'barcode'         => $product->barcode,
                    'quantity'        => $item['qty'],
                    'unit_price'      => $unitPrice,
                    'discount_type'   => $itemDiscType,
                    'discount_amount' => $itemDiscount,
                    'total_price'     => $lineTotal,
                ]);

                if ($product->track_stock) {
                    $product->decrement('stock_quantity', $item['qty']);
                }
            }

            // Payment records (only record split entries OR single payment, never duplicate)
            if ($request->payment_method === 'split' && $request->has('split_payments')) {
                foreach ($request->split_payments as $sp) {
                    SalePayment::create([
                        'sale_id'        => $sale->id,
                        'payment_method' => $sp['method'],
                        'amount'         => $sp['amount'],
                    ]);
                }
            } else {
                SalePayment::create([
                    'sale_id'        => $sale->id,
                    'payment_method' => $request->payment_method,
                    'amount'         => $paidAmount,
                ]);
            }

            // Update customer loyalty points
            if ($request->customer_id) {
                $loyaltyRate = (float) Setting::get('loyalty_rate', 1);
                $points = floor($totalAmount / 1000) * $loyaltyRate;
                Customer::where('id', $request->customer_id)->increment('loyalty_points', $points);
            }

            ActivityLog::log('sale', "Sale {$invoiceNumber} completed — Total: {$totalAmount}", $sale);

            DB::commit();

            return response()->json([
                'success'        => true,
                'invoice_number' => $invoiceNumber,
                'sale_id'        => $sale->id,
                'total_amount'   => $totalAmount,
                'change_amount'  => $changeAmount,
                'message'        => "Sale {$invoiceNumber} completed successfully!",
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()], 500);
        }
    }

    public function getReceipt($id)
    {
        $sale = Sale::with(['saleItems', 'salePayments', 'customer', 'user'])->findOrFail($id);
        $settings = [
            'store_name'     => Setting::get('store_name', 'POS System'),
            'store_address'  => Setting::get('store_address', ''),
            'store_phone'    => Setting::get('store_phone', ''),
            'currency_symbol'=> Setting::get('currency_symbol', 'Rp'),
            'receipt_footer' => Setting::get('receipt_footer', 'Terima kasih!'),
        ];
        return view('pos.receipt', compact('sale', 'settings'));
    }

    private function productPayload(Product $p): array
    {
        return [
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
            'category'       => $p->category?->name ?? '-',
            'image_url'      => $p->image_url,
            'discount_type'  => $p->discount_type,
            'discount_value' => (float)$p->discount_value,
        ];
    }
}
