<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\ActivityLog;
use App\Helpers\PosHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'user'])->latest();

        if ($s = $request->get('search')) {
            $query->where(fn($q) => $q->where('invoice_number', 'like', "%$s%")
                ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%$s%")));
        }
        if ($from = $request->get('date_from')) $query->where('created_at', '>=', $from . ' 00:00:00');
        if ($to   = $request->get('date_to'))   $query->where('created_at', '<=', $to . ' 23:59:59');
        if ($pm   = $request->get('payment_method')) $query->where('payment_method', $pm);
        if ($st   = $request->get('status')) $query->where('status', $st);

        $sales = $query->paginate(15)->withQueryString();
        return view('sales.index', compact('sales'));
    }

    public function show($id)
    {
        $sale = Sale::with(['saleItems.product', 'salePayments', 'customer', 'user', 'saleReturns'])->findOrFail($id);
        return view('sales.show', compact('sale'));
    }

    public function destroy($id)
    {
        $sale = Sale::with('saleItems.product')->findOrFail($id);
        $invoice = $sale->invoice_number;

        DB::transaction(function () use ($sale) {
            if ($sale->status !== 'refunded') {
                foreach ($sale->saleItems as $item) {
                    if ($item->product_id && $item->product?->track_stock) {
                        $alreadyReturned = \App\Models\SaleReturnItem::where('sale_item_id', $item->id)
                            ->whereHas('saleReturn', fn($q) => $q->whereNull('deleted_at'))
                            ->sum('quantity');
                        $netRestorable = max(0, $item->quantity - $alreadyReturned);
                        if ($netRestorable > 0) {
                            Product::where('id', $item->product_id)->increment('stock_quantity', $netRestorable);
                        }
                    }
                }
            }
            $sale->delete();
        });

        ActivityLog::log('delete', "Deleted sale: {$invoice}");
        return redirect()->route('sales.index')->with('success', "Sale {$invoice} deleted.");
    }

    public function export(Request $request)
    {
        $query = Sale::with(['customer', 'user'])
            ->when($request->date_from, fn($q, $v) => $q->where('created_at', '>=', $v . ' 00:00:00'))
            ->when($request->date_to,   fn($q, $v) => $q->where('created_at', '<=', $v . ' 23:59:59'))
            ->when($request->payment_method, fn($q, $v) => $q->where('payment_method', $v))
            ->latest();

        $filename = 'sales-export-' . now()->format('Ymd-His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Invoice', 'Date', 'Customer', 'Cashier', 'Payment', 'Subtotal', 'Discount', 'Tax', 'Total', 'Status']);

            foreach ($query->cursor() as $sale) {
                fputcsv($handle, [
                    PosHelper::sanitizeCsvValue($sale->invoice_number),
                    $sale->created_at->format('d/m/Y H:i'),
                    PosHelper::sanitizeCsvValue($sale->customer?->name ?? 'Walk-in'),
                    PosHelper::sanitizeCsvValue($sale->user?->name ?? '-'),
                    PosHelper::sanitizeCsvValue(ucfirst($sale->payment_method)),
                    $sale->subtotal,
                    $sale->discount_amount,
                    $sale->tax_amount,
                    $sale->total_amount,
                    ucfirst($sale->status),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
