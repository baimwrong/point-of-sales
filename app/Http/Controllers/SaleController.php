<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['customer', 'user'])->latest();

        if ($s = $request->get('search')) {
            $query->where(fn($q) => $q->where('invoice_number','ilike',"%$s%")
                ->orWhereHas('customer', fn($c) => $c->where('name','ilike',"%$s%")));
        }
        if ($from = $request->get('date_from')) $query->whereDate('created_at', '>=', $from);
        if ($to   = $request->get('date_to'))   $query->whereDate('created_at', '<=', $to);
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
        $sale = Sale::findOrFail($id);
        $invoice = $sale->invoice_number;
        $sale->delete();
        return redirect()->route('sales.index')->with('success', "Sale {$invoice} deleted.");
    }

    public function export(Request $request)
    {
        $query = Sale::with(['customer', 'user'])
            ->when($request->date_from, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($request->date_to,   fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($request->payment_method, fn($q, $v) => $q->where('payment_method', $v))
            ->latest();

        $sales = $query->get();

        $filename = 'sales-export-' . now()->format('Ymd-His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($sales) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice', 'Date', 'Customer', 'Cashier', 'Payment', 'Subtotal', 'Discount', 'Tax', 'Total', 'Status']);
            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->invoice_number,
                    $sale->created_at->format('d/m/Y H:i'),
                    $sale->customer?->name ?? 'Walk-in',
                    $sale->user?->name ?? '-',
                    ucfirst($sale->payment_method),
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
