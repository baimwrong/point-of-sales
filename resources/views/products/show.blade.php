@extends('layouts.app')
@section('title', 'Product: ' . $product->name)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">{{ $product->name }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Products</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $product->sku }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#adjustStockModal">
            <i class="fas fa-boxes-stacked me-1"></i> Adjust Stock
        </button>
        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

@include('components.alert')

<div class="row g-4">
    {{-- Left column: Image & Key stats --}}
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4 text-center p-3">
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="img-fluid rounded-3 mb-3 mx-auto" style="max-height: 240px; object-fit: contain; background: #f8fafc;">
            <h5 class="fw-bold mb-1">{{ $product->name }}</h5>
            <div class="text-muted small mb-2"><span class="badge bg-light text-dark border">{{ $product->sku }}</span></div>
            <div>
                @if($product->is_active)
                    <span class="badge bg-success-subtle text-success px-3 py-1">Active</span>
                @else
                    <span class="badge bg-danger-subtle text-danger px-3 py-1">Inactive</span>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i>Sales Performance</h6>
            </div>
            <div class="card-body p-3">
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Total Units Sold</span>
                    <strong class="text-dark">{{ number_format($totalSold, 0) }} {{ $product->unit?->abbreviation ?? 'pcs' }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Total Revenue</span>
                    <strong class="text-success">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</strong>
                </div>
                <div class="d-flex justify-content-between py-2">
                    <span class="text-muted">Profit Margin</span>
                    <strong class="text-primary">{{ $product->profit_margin }}%</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Right column: Specs and pricing --}}
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="fas fa-circle-info me-2 text-primary"></i>Product Details</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="text-muted small">Category</label>
                        <div class="fw-semibold text-dark">{{ $product->category?->name ?? 'Uncategorized' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Barcode</label>
                        <div class="fw-semibold text-dark">{{ $product->barcode ?? '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Unit</label>
                        <div class="fw-semibold text-dark">{{ $product->unit?->name ?? 'Pcs' }} ({{ $product->unit?->abbreviation ?? 'pcs' }})</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Stock Tracking</label>
                        <div class="fw-semibold text-dark">
                            {{ $product->track_stock ? 'Yes (Inventory controlled)' : 'No (Unlimited service/item)' }}
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small">Description</label>
                        <div class="text-dark">{{ $product->description ?: 'No description provided.' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="fas fa-coins me-2 text-primary"></i>Pricing & Inventory</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small">Cost Price (Modal)</div>
                            <h5 class="fw-bold mb-0 text-dark">Rp {{ number_format($product->cost_price, 0, ',', '.') }}</h5>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small">Selling Price (Jual)</div>
                            <h5 class="fw-bold mb-0 text-primary">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</h5>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small">Effective POS Price</div>
                            <h5 class="fw-bold mb-0 text-success">Rp {{ number_format($product->effective_price, 0, ',', '.') }}</h5>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small">Wholesale Price</div>
                            <h6 class="fw-bold mb-0 text-dark">
                                {{ $product->wholesale_price ? 'Rp ' . number_format($product->wholesale_price, 0, ',', '.') : '-' }}
                            </h6>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small">Current Stock</div>
                            <h6 class="fw-bold mb-0 {{ $product->is_low_stock ? 'text-danger' : 'text-dark' }}">
                                {{ $product->stock_quantity }} {{ $product->unit?->abbreviation ?? 'pcs' }}
                                @if($product->is_low_stock)
                                    <span class="badge bg-danger ms-1">Low</span>
                                @endif
                            </h6>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="p-3 bg-light rounded-3">
                            <div class="text-muted small">Min Stock Alert</div>
                            <h6 class="fw-bold mb-0 text-muted">{{ $product->min_stock_alert }} {{ $product->unit?->abbreviation ?? 'pcs' }}</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Stock Adjustment Modal --}}
<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-labelledby="adjustStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('products.updateStock', $product->id) }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="adjustStockModalLabel">Adjust Stock: {{ $product->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Stock</label>
                        <input type="text" class="form-control" value="{{ $product->stock_quantity }} {{ $product->unit?->abbreviation ?? 'pcs' }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Adjustment Type <span class="text-danger">*</span></label>
                        <select name="adjustment" class="form-select" required>
                            <option value="add">Add to stock (+)</option>
                            <option value="subtract">Subtract from stock (-)</option>
                            <option value="set">Set exact stock (=)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" min="0" required placeholder="Amount to adjust">
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">Reason / Note</label>
                        <input type="text" name="reason" class="form-control" placeholder="e.g. Stock opname, damaged goods, return">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Stock</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
