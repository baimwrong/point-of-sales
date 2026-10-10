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
            $query->where(fn($q) => $q->where('return_number','ilike',"%$s%")
                ->orWhereHas('sale', fn($sq) => $sq->where('invoice_number','ilike',"%$s%")));
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
        $request->validate([
            'sale_id'       => ['required', 'exists:sales,id'],
            'reason'        => ['nullable', 'string', 'max:500'],
            'refund_method' => ['required', 'in:cash,store_credit'],
            'items'         => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required', 'exists:sale_items,id'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::beginTransaction();

            $sale       = Sale::with('saleItems.product')->findOrFail($request->sale_id);
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

            foreach ($request->items as $retItem) {
                $saleItem  = $sale->saleItems->where('id', $retItem['sale_item_id'])->first();
                if (!$saleItem) continue;

                $retQty    = min((int)$retItem['quantity'], $saleItem->quantity);
                $unitPrice = $saleItem->unit_price;
                $lineTotal = $unitPrice * $retQty;
                $totalRefund += $lineTotal;

                SaleReturnItem::create([
                    'sale_return_id' => $saleReturn->id,
                    'sale_item_id'   => $saleItem->id,
                    'product_id'     => $saleItem->product_id,
                    'product_name'   => $saleItem->product_name,
                    'quantity'       => $retQty,
                    'unit_price'     => $unitPrice,
                    'total_price'    => $lineTotal,
                ]);

                // Restore stock
                if ($saleItem->product_id && $saleItem->product?->track_stock) {
                    Product::where('id', $saleItem->product_id)->increment('stock_quantity', $retQty);
                }
            }

            $saleReturn->update(['total_refund' => $totalRefund]);

            // Update sale status
            $sale->update(['status' => 'refunded']);

            ActivityLog::log('return', "Return {$returnNum} processed for sale {$sale->invoice_number}", $saleReturn);
            DB::commit();

            return redirect()->route('returns.show', $saleReturn->id)
                ->with('success', "Return {$returnNum} processed successfully. Refund: Rp " . number_format($totalRefund, 0, ',', '.'));
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to process return: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $return = SaleReturn::with(['sale.saleItems', 'returnItems.product', 'user'])->findOrFail($id);
        return view('returns.show', compact('return'));
    }

    public function destroy($id)
    {
        $return = SaleReturn::findOrFail($id);
        $num = $return->return_number;
        $return->delete();
        ActivityLog::log('delete', "Deleted return: {$num}");
        return redirect()->route('returns.index')->with('success', "Return {$num} deleted.");
    }
}
