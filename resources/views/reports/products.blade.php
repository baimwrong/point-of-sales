@extends('layouts.app')
@section('title', 'Product Performance Report')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Product Sales &amp; Performance Report</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('reports.index') }}" class="text-decoration-none">Reports</a></li>
                <li class="breadcrumb-item active" aria-current="page">Product Performance</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

@include('components.alert')

{{-- Filter Form --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('reports.products') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $from }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-semibold mb-1">Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $to }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-filter me-1"></i> Filter Performance
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold"><i class="fas fa-ranking-star me-2 text-primary"></i>Product Ranking by Quantity Sold</h6>
        <span class="badge bg-light text-dark border">{{ count($products) }} Products Sold</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
            <thead class="table-light">
                <tr>
                    <th style="width: 60px;">Rank</th>
                    <th>Product Name</th>
                    <th>SKU</th>
                    <th class="text-end">Selling Price</th>
                    <th class="text-center">Orders Count</th>
                    <th class="text-center">Total Quantity Sold</th>
                    <th class="text-end">Total Revenue Generated</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $index => $item)
                    <tr>
                        <td>
                            <span class="badge {{ $index < 3 ? 'bg-warning text-dark' : 'bg-light text-muted border' }} rounded-pill px-2">
                                #{{ $index + 1 }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('products.show', $item->product_id) }}" class="fw-semibold text-dark text-decoration-none">
                                {{ $item->name }}
                            </a>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $item->sku }}</span></td>
                        <td class="text-end">Rp {{ number_format($item->selling_price, 0, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($item->transaction_count, 0) }}</td>
                        <td class="text-center fw-bold">{{ number_format($item->total_sold, 0) }}</td>
                        <td class="text-end fw-bold text-success">Rp {{ number_format($item->total_revenue, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            No product sales recorded in this date range.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
