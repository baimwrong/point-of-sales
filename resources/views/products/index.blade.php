@extends('layouts.app')

@section('title', 'Products')
@section('page-title', 'Products')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Products</li>
@endsection

@section('content')

{{-- Low Stock Warning Banner --}}
@if(isset($lowStockCount) && $lowStockCount > 0)
<div class="alert alert-warning d-flex align-items-center gap-2 mb-3" role="alert">
    <i class="fas fa-triangle-exclamation fa-lg"></i>
    <div>
        <strong>Low Stock Alert!</strong>
        {{ $lowStockCount }} product(s) are running low on stock.
        <a href="?stock_status=low" class="alert-link ms-1">View low stock items →</a>
    </div>
</div>
@endif

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Products</h4>
        <p class="text-muted mb-0 small">Manage your product catalog</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('products.export') }}" class="btn btn-outline-success">
            <i class="fas fa-file-excel me-1"></i> Export Catalog
        </a>
        <a href="{{ route('products.import') }}" class="btn btn-outline-primary">
            <i class="fas fa-file-arrow-up me-1"></i> Import Excel/CSV
        </a>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Product
        </a>
    </div>
</div>

@if(session('import_errors'))
<div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
    <div class="fw-bold mb-1"><i class="fas fa-triangle-exclamation me-1"></i>Import Warnings:</div>
    <ul class="mb-0 small ps-3">
        @foreach(session('import_errors') as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Filters Card --}}
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label mb-1 small fw-semibold">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Name, SKU, barcode…" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label mb-1 small fw-semibold">Category</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories ?? [] as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small fw-semibold">Stock Status</label>
                <select name="stock_status" class="form-select form-select-sm">
                    <option value="">All Stock</option>
                    <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock</option>
                    <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1 small fw-semibold">Status</label>
                <select name="is_active" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-1">
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i></button>
                    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-xmark"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Products Table --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between py-3">
        <span class="fw-semibold">
            <i class="fas fa-box me-2 text-primary"></i>
            Product List
            @if(isset($products))
            <span class="badge bg-primary ms-1">{{ $products->total() ?? count($products) }}</span>
            @endif
        </span>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" title="Export CSV">
                <i class="fas fa-file-csv me-1"></i>Export
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:56px">#</th>
                    <th style="width:60px">Image</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Cost</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th style="width:130px" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products ?? [] as $product)
                <tr>
                    <td class="text-muted small">{{ $loop->iteration }}</td>
                    <td>
                        @if($product->image)
                        <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}"
                             style="width:48px;height:48px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6">
                        @else
                        <div style="width:48px;height:48px;background:#f0f2f5;border-radius:8px;display:flex;align-items:center;justify-content:center">
                            <i class="fas fa-image text-muted"></i>
                        </div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $product->name }}</div>
                        <div class="text-muted small">SKU: {{ $product->sku ?? '—' }}</div>
                    </td>
                    <td>
                        @if($product->category)
                        <span class="badge bg-info text-dark">{{ $product->category->name }}</span>
                        @else
                        <span class="text-muted small">—</span>
                        @endif
                    </td>
                    <td class="text-muted">${{ number_format($product->cost_price ?? 0, 2) }}</td>
                    <td class="fw-semibold">${{ number_format($product->selling_price ?? 0, 2) }}</td>
                    <td>
                        @php
                            $stock = $product->stock_quantity ?? 0;
                            $minStock = $product->min_stock_alert ?? 5;
                        @endphp
                        @if($stock <= 0)
                            <span class="badge bg-danger">Out of Stock</span>
                        @elseif($stock <= $minStock)
                            <span class="badge bg-warning text-dark">Low: {{ $stock }}</span>
                        @else
                            <span class="badge bg-success">{{ $stock }}</span>
                        @endif
                    </td>
                    <td>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   {{ $product->is_active ? 'checked' : '' }}
                                   onchange="toggleStatus(this, {{ $product->id }})">
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('products.show', $product) }}" class="btn btn-outline-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary" title="Edit">
                                <i class="fas fa-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-outline-danger" title="Delete"
                                    onclick="confirmDelete('delete-product-{{ $product->id }}', '{{ $product->name }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <form id="delete-product-{{ $product->id }}" action="{{ route('products.destroy', $product) }}" method="POST" class="d-none">
                            @csrf @method('DELETE')
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <div class="text-muted">
                            <i class="fas fa-box-open fa-3x mb-3 d-block opacity-25"></i>
                            No products found.
                            <a href="{{ route('products.create') }}" class="d-block mt-2 text-primary">Add your first product →</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($products) && method_exists($products, 'links'))
    <div class="card-footer bg-white py-3">
        {{ $products->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
function toggleStatus(el, productId) {
    fetch(`/products/${productId}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        }
    }).then(r => r.json()).then(data => {
        Swal.fire({ icon: 'success', title: 'Updated', text: data.message || 'Status updated', timer: 1500, showConfirmButton: false });
    }).catch(() => {
        el.checked = !el.checked;
        Swal.fire({ icon: 'error', title: 'Error', text: 'Could not update status' });
    });
}
</script>
@endpush
