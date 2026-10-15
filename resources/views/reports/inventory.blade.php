@extends('layouts.app')
@section('title', 'Inventory Valuation Report')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Inventory Valuation &amp; Stock Status</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" class="text-decoration-none">Reports</a></li>
                <li class="breadcrumb-item active" aria-current="page">Inventory</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> New Purchase Order
        </a>
        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

{{-- Inventory KPI Summary --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Total Inventory Value (Asset Cost)</div>
                <h3 class="fw-bold text-primary mb-0">Rp {{ number_format($totalValue, 0, ',', '.') }}</h3>
                <div class="text-muted small mt-1">Based on current stock &times; cost price</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Low Stock Products</div>
                <h3 class="fw-bold text-warning mb-0">{{ $lowStockCount }} Items</h3>
                <div class="text-muted small mt-1">Stock &le; minimum alert threshold</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="text-muted small text-uppercase fw-semibold mb-1">Depleted / Out of Stock</div>
                <h3 class="fw-bold text-danger mb-0">{{ $outOfStock }} Items</h3>
                <div class="text-muted small mt-1">Requires urgent re-order</div>
            </div>
        </div>
    </div>
</div>

{{-- Inventory Table --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold"><i class="fas fa-boxes-stacked me-2 text-primary"></i>Tracked Inventory Items</h6>
        <span class="badge bg-light text-dark border">{{ count($products) }} Tracked Products</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th class="text-end">Cost Price</th>
                    <th class="text-center">Stock Level</th>
                    <th class="text-center">Min Alert</th>
                    <th class="text-end">Total Valuation</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $prod)
                    <tr class="{{ $prod->stock_quantity <= 0 ? 'table-danger' : ($prod->is_low_stock ? 'table-warning' : '') }}">
                        <td>
                            <a href="{{ route('products.show', $prod->id) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $prod->name }}
                            </a>
                            <div class="text-muted small">SKU: {{ $prod->sku }}</div>
                        </td>
                        <td>{{ $prod->category?->name ?? 'Uncategorized' }}</td>
                        <td>{{ $prod->unit?->abbreviation ?? 'pcs' }}</td>
                        <td class="text-end">Rp {{ number_format($prod->cost_price, 0, ',', '.') }}</td>
                        <td class="text-center fw-bold">
                            @if($prod->stock_quantity <= 0)
                                <span class="badge bg-danger">0 (Out of stock)</span>
                            @elseif($prod->is_low_stock)
                                <span class="badge bg-warning text-dark">{{ $prod->stock_quantity }} (Low)</span>
                            @else
                                <span class="badge bg-success-subtle text-success">{{ $prod->stock_quantity }}</span>
                            @endif
                        </td>
                        <td class="text-center text-muted">{{ $prod->min_stock_alert }}</td>
                        <td class="text-end fw-bold text-dark">
                            Rp {{ number_format($prod->stock_quantity * $prod->cost_price, 0, ',', '.') }}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('products.show', $prod->id) }}" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            No inventory tracked products found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
