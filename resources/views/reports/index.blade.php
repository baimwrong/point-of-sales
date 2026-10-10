@extends('layouts.app')
@section('title', 'Reports & Analytics')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Reports &amp; Analytics Dashboard</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Reports</li>
            </ol>
        </nav>
    </div>
</div>

@include('components.alert')

{{-- Quick Summary Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">Today's Performance</span>
                    <span class="badge bg-primary-subtle text-primary">{{ $stats['today_count'] }} sales</span>
                </div>
                <h3 class="fw-bold text-dark mb-0">Rp {{ number_format($stats['today_revenue'], 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">This Week</span>
                    <span class="badge bg-info-subtle text-info">{{ $stats['week_count'] }} sales</span>
                </div>
                <h3 class="fw-bold text-dark mb-0">Rp {{ number_format($stats['week_revenue'], 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small text-uppercase fw-semibold">This Month</span>
                    <span class="badge bg-success-subtle text-success">{{ $stats['month_count'] }} sales</span>
                </div>
                <h3 class="fw-bold text-dark mb-0">Rp {{ number_format($stats['month_revenue'], 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>
</div>

{{-- Report Navigation Hub --}}
<div class="row g-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100 text-center p-4">
            <div class="p-3 bg-primary-subtle text-primary rounded-circle mx-auto mb-3" style="width: 64px; height: 64px; font-size: 1.5rem; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-chart-line"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">Sales Revenue Report</h5>
            <p class="text-muted small mb-4">Detailed breakdown of sales, income, taxes, payment methods, and daily revenue trends.</p>
            <a href="{{ route('reports.sales') }}" class="btn btn-primary mt-auto">
                <i class="fas fa-chart-line me-1"></i> Open Sales Report
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100 text-center p-4">
            <div class="p-3 bg-success-subtle text-success rounded-circle mx-auto mb-3" style="width: 64px; height: 64px; font-size: 1.5rem; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-cubes-stacked"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">Product Performance</h5>
            <p class="text-muted small mb-4">Identify top-selling items, product revenues, quantities sold, and gross profitability.</p>
            <a href="{{ route('reports.products') }}" class="btn btn-success mt-auto">
                <i class="fas fa-cubes me-1"></i> Open Product Report
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100 text-center p-4">
            <div class="p-3 bg-warning-subtle text-warning-emphasis rounded-circle mx-auto mb-3" style="width: 64px; height: 64px; font-size: 1.5rem; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-warehouse"></i>
            </div>
            <h5 class="fw-bold text-dark mb-2">Inventory Valuation</h5>
            <p class="text-muted small mb-4">Current stock valuation, asset cost levels, depleted inventory, and low stock warnings.</p>
            <a href="{{ route('reports.inventory') }}" class="btn btn-warning mt-auto text-dark">
                <i class="fas fa-boxes-stacked me-1"></i> Open Inventory Report
            </a>
        </div>
    </div>
</div>
@endsection
