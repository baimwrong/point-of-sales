@extends('layouts.app')
@section('title', 'Dashboard')

@section('styles')
<style>
.stat-card { border-radius: 16px; border: none; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; }
.stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(0,0,0,0.1) !important; }
.stat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
.chart-card { border-radius: 16px; border: none; }
.product-rank { width: 28px; height: 28px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; font-size: .75rem; font-weight: 700; }
</style>
@endsection

@section('content')
<div class="animate-fade-up">

{{-- Stats Row --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3 animate-fade-up animate-delay-1">
        <div class="card stat-card h-100" style="box-shadow:0 4px 20px rgba(37,99,235,.12)">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="stat-icon" style="background:#eff6ff;color:#2563eb">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="flex-fill">
                    <div class="text-muted mb-1" style="font-size:.78rem;font-weight:600;letter-spacing:.04em;text-transform:uppercase">Today's Sales</div>
                    <div class="fw-bold" style="font-size:1.6rem;color:#1e293b;line-height:1">{{ $todaySalesCount }}</div>
                    <div class="text-muted mt-1" style="font-size:.78rem">transactions</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3 animate-fade-up animate-delay-2">
        <div class="card stat-card h-100" style="box-shadow:0 4px 20px rgba(16,185,129,.12)">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="stat-icon" style="background:#f0fdf4;color:#10b981">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="flex-fill">
                    <div class="text-muted mb-1" style="font-size:.78rem;font-weight:600;letter-spacing:.04em;text-transform:uppercase">Today's Revenue</div>
                    <div class="fw-bold" style="font-size:1.2rem;color:#1e293b;line-height:1.2">
                        Rp {{ number_format($todayRevenue, 0, ',', '.') }}
                    </div>
                    <div class="text-muted mt-1" style="font-size:.78rem">total collected</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3 animate-fade-up animate-delay-3">
        <div class="card stat-card h-100" style="box-shadow:0 4px 20px rgba(139,92,246,.12)">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="stat-icon" style="background:#f5f3ff;color:#8b5cf6">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="flex-fill">
                    <div class="text-muted mb-1" style="font-size:.78rem;font-weight:600;letter-spacing:.04em;text-transform:uppercase">Monthly Revenue</div>
                    <div class="fw-bold" style="font-size:1.2rem;color:#1e293b;line-height:1.2">
                        Rp {{ number_format($monthlyRevenue, 0, ',', '.') }}
                    </div>
                    <div class="text-muted mt-1" style="font-size:.78rem">{{ $monthlyCount }} transactions</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3 animate-fade-up animate-delay-4">
        <div class="card stat-card h-100" style="box-shadow:0 4px 20px rgba(239,68,68,.12)">
            <div class="card-body d-flex align-items-center gap-3 p-4">
                <div class="stat-icon" style="background:#fef2f2;color:#ef4444">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <div class="flex-fill">
                    <div class="text-muted mb-1" style="font-size:.78rem;font-weight:600;letter-spacing:.04em;text-transform:uppercase">Low Stock</div>
                    <div class="fw-bold" style="font-size:1.6rem;color:#1e293b;line-height:1">{{ $lowStockCount }}</div>
                    <div class="text-muted mt-1" style="font-size:.78rem">
                        @if($lowStockCount > 0)
                            <a href="{{ route('reports.inventory') }}" class="text-danger">View details</a>
                        @else
                            <span class="text-success">All stocked</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Chart + Top Products --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card chart-card h-100" style="box-shadow:0 2px 12px rgba(0,0,0,.06)">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <div class="fw-bold" style="color:#1e293b">Sales Last 7 Days</div>
                    <div class="text-muted mt-1" style="font-size:.78rem">Daily revenue trend</div>
                </div>
                <a href="{{ route('reports.sales') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-chart-bar me-1"></i>Full Report
                </a>
            </div>
            <div class="card-body p-3" style="height:280px">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100" style="box-shadow:0 2px 12px rgba(0,0,0,.06)">
            <div class="card-header">
                <div class="fw-bold" style="color:#1e293b">Top Products</div>
                <div class="text-muted mt-1" style="font-size:.78rem">Best sellers this month</div>
            </div>
            <div class="card-body p-0">
                @forelse($topProducts as $i => $product)
                    <div class="d-flex align-items-center gap-3 px-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="product-rank" style="background:{{ ['#eff6ff','#f0fdf4','#fef9c3','#fdf4ff','#fff7ed'][$i] ?? '#f8fafc' }};color:{{ ['#2563eb','#10b981','#ca8a04','#9333ea','#ea580c'][$i] ?? '#64748b' }}">
                            {{ $i + 1 }}
                        </div>
                        <div class="flex-fill min-w-0">
                            <div class="fw-semibold text-truncate" style="font-size:.85rem;color:#1e293b">{{ $product->name }}</div>
                            <div class="text-muted" style="font-size:.75rem">{{ number_format($product->total_sold, 0) }} units sold</div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold text-success" style="font-size:.82rem">Rp {{ number_format($product->total_revenue, 0, ',', '.') }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4" style="font-size:.85rem">
                        <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>No sales data this month
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Recent Sales + Low Stock --}}
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card" style="box-shadow:0 2px 12px rgba(0,0,0,.06)">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="fw-bold" style="color:#1e293b">Recent Sales</div>
                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" style="font-size:.855rem">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                            <tr>
                                <td>
                                    <a href="{{ route('sales.show', $sale->id) }}" class="fw-semibold text-primary text-decoration-none">
                                        {{ $sale->invoice_number }}
                                    </a>
                                </td>
                                <td class="text-muted">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                                <td class="fw-semibold">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                <td>{!! $sale->payment_method_badge !!}</td>
                                <td class="text-muted">{{ $sale->created_at->format('H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No sales today</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card h-100" style="box-shadow:0 2px 12px rgba(0,0,0,.06)">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div class="fw-bold" style="color:#1e293b">
                    <i class="fas fa-triangle-exclamation text-warning me-1"></i>Low Stock Alert
                </div>
                <a href="{{ route('reports.inventory') }}" class="btn btn-sm btn-outline-secondary">View All</a>
            </div>
            <div class="card-body p-0">
                @forelse($lowStockProducts as $product)
                    <div class="d-flex align-items-center gap-2 px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="flex-fill min-w-0">
                            <div class="fw-semibold text-truncate" style="font-size:.84rem">{{ $product->name }}</div>
                            <div class="text-muted" style="font-size:.75rem">{{ $product->category?->name ?? 'Uncategorized' }}</div>
                        </div>
                        <div>
                            @if($product->stock_quantity == 0)
                                <span class="badge bg-danger">Out of Stock</span>
                            @else
                                <span class="badge bg-warning text-dark">{{ $product->stock_quantity }} left</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-check-circle text-success fa-2x mb-2 d-block"></i>
                        All products are well stocked!
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

</div>
@endsection

@section('scripts')
<script>
(function() {
    const ctx = document.getElementById('salesChart');
    if (!ctx) return;
    const labels = @json($chartLabels);
    const data   = @json($chartData);
    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Revenue (Rp)',
                data,
                fill: true,
                backgroundColor: 'rgba(37, 99, 235, 0.08)',
                borderColor: '#2563eb',
                borderWidth: 2.5,
                pointBackgroundColor: '#2563eb',
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.parsed.y)
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        callback: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v),
                        font: { size: 11 }
                    }
                },
                x: { grid: { display: false }, ticks: { font: { size: 11 } } }
            }
        }
    });
})();
</script>
@endsection
