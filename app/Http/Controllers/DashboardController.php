<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Today's stats
        $todaySalesCount   = Sale::completed()->today()->count();
        $todayRevenue      = Sale::completed()->today()->sum('total_amount');
        $monthlyRevenue    = Sale::completed()->thisMonth()->sum('total_amount');
        $monthlyCount      = Sale::completed()->thisMonth()->count();

        // Inventory
        $totalProducts     = Product::active()->count();
        $lowStockCount     = Product::lowStock()->count();

        // Sales chart — last 7 days
        $chartData   = [];
        $chartLabels = [];
        $startDate   = now()->subDays(6)->startOfDay();

        $dailySales = Sale::completed()
            ->where('created_at', '>=', $startDate)
            ->selectRaw("DATE(created_at) as sale_date, SUM(total_amount) as daily_total")
            ->groupByRaw("DATE(created_at)")
            ->pluck('daily_total', 'sale_date');

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chartLabels[] = $date->format('d M');
            $chartData[]   = (float) ($dailySales[$date->toDateString()] ?? 0);
        }

        // Top 5 selling products this month (SARGable range, excludes soft-deleted)
        $monthStart = now()->startOfMonth()->startOfDay();
        $monthEnd   = now()->endOfMonth()->endOfDay();

        $topProducts = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.created_at', [$monthStart, $monthEnd])
            ->where('sales.status', 'completed')
            ->whereNull('sales.deleted_at')
            ->whereNull('products.deleted_at')
            ->groupBy('sale_items.product_id', 'products.name', 'products.image')
            ->select(
                'sale_items.product_id',
                'products.name',
                'products.image',
                DB::raw('SUM(sale_items.quantity) as total_sold'),
                DB::raw('SUM(sale_items.total_price) as total_revenue')
            )
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // Recent sales
        $recentSales = Sale::with(['customer', 'user'])
            ->latest()
            ->limit(10)
            ->get();

        // Low stock products
        $lowStockProducts = Product::lowStock()
            ->with('category')
            ->orderBy('stock_quantity')
            ->limit(8)
            ->get();

        // Total purchase this month (excluding cancelled)
        $monthlyPurchases = Purchase::whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('status', '!=', 'cancelled')
            ->sum('total_amount');

        return view('dashboard.index', compact(
            'todaySalesCount', 'todayRevenue', 'monthlyRevenue', 'monthlyCount',
            'totalProducts', 'lowStockCount', 'chartLabels', 'chartData',
            'topProducts', 'recentSales', 'lowStockProducts', 'monthlyPurchases'
        ));
    }
}
