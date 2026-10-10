<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $today  = Sale::today()->where('status','completed');
        $week   = Sale::whereBetween('created_at',[now()->startOfWeek(), now()->endOfWeek()])->where('status','completed');
        $month  = Sale::thisMonth()->where('status','completed');

        $stats = [
            'today_count'    => $today->count(),
            'today_revenue'  => $today->sum('total_amount'),
            'week_count'     => $week->count(),
            'week_revenue'   => $week->sum('total_amount'),
            'month_count'    => $month->count(),
            'month_revenue'  => $month->sum('total_amount'),
        ];
        return view('reports.index', compact('stats'));
    }

    public function sales(Request $request)
    {
        $from = $request->get('date_from', now()->startOfMonth()->toDateString());
        $to   = $request->get('date_to',   now()->toDateString());
        $pm   = $request->get('payment_method');

        $query = Sale::with(['customer','user'])
            ->where('status', 'completed')
            ->dateRange($from, $to);
        if ($pm) $query->where('payment_method', $pm);

        $sales = $query->latest()->get();

        // Daily totals for chart
        $dailyData   = $sales->groupBy(fn($s) => $s->created_at->format('d/m'))->map(fn($g) => $g->sum('total_amount'));
        $chartLabels = $dailyData->keys()->toArray();
        $chartData   = $dailyData->values()->toArray();

        $summary = [
            'count'         => $sales->count(),
            'revenue'       => $sales->sum('total_amount'),
            'avg_order'     => $sales->count() > 0 ? $sales->sum('total_amount') / $sales->count() : 0,
            'total_discount'=> $sales->sum('discount_amount'),
            'total_tax'     => $sales->sum('tax_amount'),
        ];

        return view('reports.sales', compact('sales', 'summary', 'chartLabels', 'chartData', 'from', 'to', 'pm'));
    }

    public function products(Request $request)
    {
        $from = $request->get('date_from', now()->startOfMonth()->toDateString());
        $to   = $request->get('date_to',   now()->toDateString());

        $products = DB::table('sale_items')
            ->join('sales',    'sales.id',    '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.status', 'completed')
            ->whereDate('sales.created_at', '>=', $from)
            ->whereDate('sales.created_at', '<=', $to)
            ->groupBy('sale_items.product_id', 'products.name', 'products.sku', 'products.selling_price', 'products.cost_price')
            ->select(
                'sale_items.product_id',
                'products.name',
                'products.sku',
                'products.selling_price',
                'products.cost_price',
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('SUM(sale_items.total_price) as total_revenue'),
                DB::raw('COUNT(DISTINCT sale_items.sale_id) as transaction_count')
            )
            ->orderByDesc('total_sold')
            ->get();

        return view('reports.products', compact('products', 'from', 'to'));
    }

    public function inventory()
    {
        $products = Product::with(['category', 'unit'])
            ->where('track_stock', true)
            ->orderBy('stock_quantity')
            ->get();

        $totalValue    = $products->sum(fn($p) => $p->stock_quantity * $p->cost_price);
        $lowStockCount = $products->filter(fn($p) => $p->is_low_stock)->count();
        $outOfStock    = $products->where('stock_quantity', 0)->count();

        return view('reports.inventory', compact('products', 'totalValue', 'lowStockCount', 'outOfStock'));
    }

    public function export(Request $request)
    {
        $from = $request->get('date_from', now()->startOfMonth()->toDateString());
        $to   = $request->get('date_to',   now()->toDateString());

        $sales = Sale::with(['customer', 'user', 'saleItems'])
            ->where('status', 'completed')
            ->dateRange($from, $to)
            ->latest()
            ->get();

        $filename = "report-{$from}-to-{$to}.csv";
        $headers  = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];

        $callback = function () use ($sales) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice','Date','Customer','Cashier','Payment','Items','Subtotal','Discount','Tax','Total']);
            foreach ($sales as $sale) {
                fputcsv($handle, [
                    $sale->invoice_number,
                    $sale->created_at->format('d/m/Y H:i'),
                    $sale->customer?->name ?? 'Walk-in',
                    $sale->user?->name ?? '-',
                    ucfirst($sale->payment_method),
                    $sale->saleItems->count(),
                    $sale->subtotal,
                    $sale->discount_amount,
                    $sale->tax_amount,
                    $sale->total_amount,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
