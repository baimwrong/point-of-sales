<?php

namespace App\Http\Controllers;

use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Models\Sale;
use App\Models\Product;
use App\Models\ActivityLog;
use App\Helpers\PosHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = SaleReturn::with(['sale', 'user'])->latest();
        if ($s = $request->get('search')) {
            $query->where(fn($q) => $q->where('return_number', 'like', "%$s%")
                ->orWhereHas('sale', fn($sq) => $sq->where('invoice_number', 'like', "%$s%")));
        }
        $returns = $query->paginate(15)->withQueryString();
        return view('returns.index', compact('returns'));
    }

    public function create(Request $request)
    {
        $saleId = $request->get('sale_id');
        $sale   = $saleId ? Sale::with('saleItems.product')->findOrFail($saleId) : null;
        $sales  = Sale::completed()->latest()->limit(50)->get(['id', 'invoice_number', 'created_at']);
        return view('returns.create', compact('sale', 'sales'));
    }

    public function store(Request $request)
    {
        // Filter out items with 0 or negative return quantity so partial returns validate cleanly
        $items = array_values(array_filter($request->input('items', []), fn($i) => isset($i['quantity']) && (int)$i['quantity'] > 0));
        $request->merge(['items' => $items]);

        $request->validate([
            'sale_id'              => ['required', 'exists:sales,id'],
            'reason'               => ['nullable', 'string', 'max:500'],
            'refund_method'        => ['required', 'in:cash,store_credit'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'exists:sale_items,id'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
        ], [
            'items.min' => 'Please specify a return quantity of at least 1 for at least one item.',
        ]);

        try {
            DB::beginTransaction();

            $sale       = Sale::with('saleItems.product')->lockForUpdate()->findOrFail($request->sale_id);
            $returnNum  = PosHelper::generateReturnNumber();
            $totalRefund = 0;

            $saleReturn = SaleReturn::create([
                'return_number' => $returnNum,
                'sale_id'       => $sale->id,
                'user_id'       => auth()->id(),
                'reason'        => $request->reason,
                'status'        => 'approved',
                'refund_method' => $request->refund_method,
                'total_refund'  => 0,
                'notes'         => $request->notes,
            ]);

            $hasReturnItems = false;
            foreach ($request->items as $retItem) {
                $saleItem = $sale->saleItems->where('id', $retItem['sale_item_id'])->first();
                if (!$saleItem) continue;

                $alreadyReturned = SaleReturnItem::where('sale_item_id', $saleItem->id)
                    ->whereHas('saleReturn', fn($q) => $q->whereNull('deleted_at'))
                    ->sum('quantity');
                $maxReturnable   = $saleItem->quantity - $alreadyReturned;

                if ($maxReturnable <= 0) {
                    continue;
                }

                $retQty = min((int)$retItem['quantity'], $maxReturnable);
                if ($retQty <= 0) {
                    continue;
                }

                $hasReturnItems = true;
                // Calculate refund using effective unit price (respecting line item discount)
                $effectiveUnitPrice = $saleItem->quantity > 0
                    ? ($saleItem->total_price / $saleItem->quantity)
                    : (float)$saleItem->unit_price;

                // Proportionately factor in order-level discount and tax
                $orderDiscountRatio = $sale->subtotal > 0 ? ((float)$sale->discount_amount / (float)$sale->subtotal) : 0;
                $netUnitRefund      = $effectiveUnitPrice * (1 - $orderDiscountRatio);
                $unitTaxRefund      = $sale->tax_rate > 0 ? ($netUnitRefund * ((float)$sale->tax_rate / 100)) : 0;
                $finalUnitRefund    = round($netUnitRefund + $unitTaxRefund, 2);

                $lineTotal = round($finalUnitRefund * $retQty, 2);
                $totalRefund += $lineTotal;

                SaleReturnItem::create([
                    'sale_return_id' => $saleReturn->id,
                    'sale_item_id'   => $saleItem->id,
                    'product_id'     => $saleItem->product_id,
                    'product_name'   => $saleItem->product_name,
                    'quantity'       => $retQty,
                    'unit_price'     => $finalUnitRefund,
                    'total_price'    => $lineTotal,
                ]);

                // Restore stock
                if ($saleItem->product_id) {
                    $product = $saleItem->product ?? Product::withTrashed()->find($saleItem->product_id);
                    if ($product && $product->track_stock) {
                        $product->increment('stock_quantity', $retQty);
                    }
                }
            }

            if (!$hasReturnItems) {
                DB::rollBack();
                return back()->with('error', 'No valid items to return. The selected items have already been fully returned.')->withInput();
            }

            $saleReturn->update(['total_refund' => $totalRefund]);

            // Reverse customer loyalty points proportionally
            if ($sale->customer_id) {
                $loyaltyRate = (float) Setting::get('loyalty_rate', 1);
                $pointsDeducted = floor($totalRefund / 1000) * $loyaltyRate;
                if ($pointsDeducted > 0) {
                    $customer = Customer::find($sale->customer_id);
                    if ($customer && $customer->loyalty_points > 0) {
                        $customer->decrement('loyalty_points', min($customer->loyalty_points, $pointsDeducted));
                    }
                }
            }

            // Update sale status based on whether all items or some items are returned
            $totalSaleQty     = $sale->saleItems->sum('quantity');
            $totalReturnedQty = SaleReturnItem::whereIn('sale_item_id', $sale->saleItems->pluck('id'))
                ->whereHas('saleReturn', fn($q) => $q->whereNull('deleted_at'))
                ->sum('quantity');
            $saleStatus       = ($totalReturnedQty >= $totalSaleQty) ? 'refunded' : 'partial_refund';
            $sale->update(['status' => $saleStatus]);

            ActivityLog::log('return', "Return {$returnNum} processed for sale {$sale->invoice_number}", $saleReturn);
            DB::commit();

            return redirect()->route('returns.show', $saleReturn->id)
                ->with('success', "Return {$returnNum} processed successfully. Refund: Rp " . number_format($totalRefund, 0, ',', '.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $msg = config('app.debug') ? $e->getMessage() : 'An error occurred while processing the return.';
            return back()->with('error', 'Failed to process return: ' . $msg)->withInput();
        }
    }

    public function show($id)
    {
        $return = SaleReturn::with(['sale.saleItems', 'returnItems.product', 'user'])->findOrFail($id);
        return view('returns.show', compact('return'));
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $return = SaleReturn::with(['returnItems.product', 'sale.saleItems'])->findOrFail($id);
            $num    = $return->return_number;
            $sale   = $return->sale;

            // Revert restored stock
            foreach ($return->returnItems as $item) {
                if ($item->product_id) {
                    $product = $item->product ?? Product::withTrashed()->find($item->product_id);
                    if ($product && $product->track_stock) {
                        $product->decrement('stock_quantity', min($product->stock_quantity, $item->quantity));
                    }
                }
            }

            $return->delete();

            // Recalculate parent sale status
            if ($sale) {
                $totalSaleQty = $sale->saleItems->sum('quantity');
                $remainingReturnedQty = SaleReturnItem::whereIn('sale_item_id', $sale->saleItems->pluck('id'))
                    ->whereHas('saleReturn', fn($q) => $q->whereNull('deleted_at'))
                    ->sum('quantity');

                if ($remainingReturnedQty == 0) {
                    $sale->update(['status' => 'completed']);
                } elseif ($remainingReturnedQty >= $totalSaleQty) {
                    $sale->update(['status' => 'refunded']);
                } else {
                    $sale->update(['status' => 'partial_refund']);
                }
            }

            ActivityLog::log('delete', "Deleted return: {$num}");
            DB::commit();

            return redirect()->route('returns.index')->with('success', "Return {$num} deleted.");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            $msg = config('app.debug') ? $e->getMessage() : 'An error occurred while deleting the return.';
            return back()->with('error', 'Failed to delete return: ' . $msg);
        }
    }
}
