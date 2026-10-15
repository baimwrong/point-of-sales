@extends('layouts.app')
@section('title', 'Sales Report')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Sales &amp; Revenue Report</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" class="text-decoration-none">Reports</a></li>
                <li class="breadcrumb-item active" aria-current="page">Sales Report</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.export', ['date_from' => $from, 'date_to' => $to]) }}" class="btn btn-outline-success">
            <i class="fas fa-file-csv me-1"></i> Export CSV
        </a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

{{-- Filter Form --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('reports.sales') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $from }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $to }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Payment Method</label>
                <select name="payment_method" class="form-select form-select-sm">
                    <option value="">All Methods</option>
                    <option value="cash" {{ $pm === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="card" {{ $pm === 'card' ? 'selected' : '' }}>Card</option>
                    <option value="transfer" {{ $pm === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="qris" {{ $pm === 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="split" {{ $pm === 'split' ? 'selected' : '' }}>Split</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-filter me-1"></i> Generate Report
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <div class="text-muted small">Total Transactions</div>
                <h4 class="fw-bold mb-0 text-dark">{{ number_format($summary['count'], 0) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <div class="text-muted small">Total Net Revenue</div>
                <h4 class="fw-bold mb-0 text-success">Rp {{ number_format($summary['revenue'], 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <div class="text-muted small">Average Order Value</div>
                <h4 class="fw-bold mb-0 text-primary">Rp {{ number_format($summary['avg_order'], 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3">
                <div class="text-muted small">Total Tax Collected</div>
                <h4 class="fw-bold mb-0 text-info">Rp {{ number_format($summary['total_tax'], 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>

{{-- Chart Card --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="mb-0 fw-bold"><i class="fas fa-chart-column me-2 text-primary"></i>Daily Revenue Trend ({{ $from }} &mdash; {{ $to }})</h6>
    </div>
    <div class="card-body p-3" style="height: 280px;">
        <canvas id="reportChart"></canvas>
    </div>
</div>

{{-- Detailed Table --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom">
        <h6 class="mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i>Sales Records in Selected Period</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
            <thead class="table-light">
                <tr>
                    <th>Invoice #</th>
                    <th>Date & Time</th>
                    <th>Customer</th>
                    <th>Payment</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-end">Discount</th>
                    <th class="text-end">Tax</th>
                    <th class="text-end">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td>
                            <a href="{{ route('sales.show', $sale->id) }}" class="fw-bold text-primary text-decoration-none">
                                {{ $sale->invoice_number }}
                            </a>
                        </td>
                        <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                        <td>{!! $sale->payment_method_badge !!}</td>
                        <td class="text-end">Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</td>
                        <td class="text-end text-danger">{{ $sale->discount_amount > 0 ? '-Rp ' . number_format($sale->discount_amount, 0, ',', '.') : '-' }}</td>
                        <td class="text-end">Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            No sales transactions recorded in this date range.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
    const ctx = document.getElementById('reportChart');
    if (!ctx) return;
    const labels = @json($chartLabels);
    const data   = @json($chartData);
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Revenue (Rp)',
                data,
                backgroundColor: 'rgba(37, 99, 235, 0.75)',
                borderColor: '#2563eb',
                borderRadius: 6,
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
                    ticks: {
                        callback: v => 'Rp ' + new Intl.NumberFormat('id-ID').format(v)
                    }
                }
            }
        }
    });
})();
</script>
@endsection
