@extends('layouts.app')
@section('title', 'Bulk Import Products')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Bulk Import Products via Excel / CSV</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Products</a></li>
                <li class="breadcrumb-item active" aria-current="page">Bulk Import</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('products.import.template') }}" class="btn btn-outline-success">
            <i class="fas fa-file-excel me-1"></i> Download Sample Excel/CSV
        </a>
        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Upload Form --}}
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="fas fa-file-arrow-up me-2 text-primary"></i>Upload Spreadsheet</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('products.import.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Choose Spreadsheet File (.xlsx or .csv) <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control form-control-lg @error('file') is-invalid @enderror" accept=".xlsx,.csv,.txt" required>
                        <div class="form-text text-muted small mt-1">Supports Microsoft Excel (.xlsx) and standard CSV files up to 10MB.</div>
                        @error('file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-4 border">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="update_existing" id="updateExisting" value="1" checked>
                            <label class="form-check-label fw-semibold text-dark" for="updateExisting">
                                Update existing products if SKU matches
                            </label>
                            <div class="small text-muted mt-1">If unchecked, rows with duplicate SKUs will be skipped without modifying existing records.</div>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary py-2 fw-semibold">
                            <i class="fas fa-cloud-arrow-up me-1"></i> Upload and Import Products
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Categories and Units Reference --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="fas fa-circle-info me-2 text-primary"></i>System Reference Guide</h6>
            </div>
            <div class="card-body p-4">
                <h6 class="fw-semibold text-dark mb-2">Existing Categories:</h6>
                <div class="d-flex flex-wrap gap-1 mb-3">
                    @forelse($categories as $cat)
                        <span class="badge bg-light text-dark border">{{ $cat->name }}</span>
                    @empty
                        <span class="text-muted small">None yet (new categories in Excel will be auto-created!)</span>
                    @endforelse
                </div>

                <h6 class="fw-semibold text-dark mb-2">Existing Units:</h6>
                <div class="d-flex flex-wrap gap-1">
                    @forelse($units as $u)
                        <span class="badge bg-light text-dark border">{{ $u->abbreviation }} ({{ $u->name }})</span>
                    @empty
                        <span class="text-muted small">None yet (default is pcs)</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- File Format Guide --}}
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="fas fa-table me-2 text-primary"></i>Spreadsheet Columns Specification</h6>
            </div>
            <div class="card-body p-4">
                <p class="text-muted small">Make sure the first row in your Excel or CSV file contains these header column names:</p>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle" style="font-size: .83rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Column Header</th>
                                <th>Required?</th>
                                <th>Description / Example</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>name</code></td>
                                <td><span class="badge bg-danger">Required</span></td>
                                <td>Product name (e.g. <em>Kopi Arabika 250g</em>)</td>
                            </tr>
                            <tr>
                                <td><code>sku</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Unique item code. If blank, auto-generated.</td>
                            </tr>
                            <tr>
                                <td><code>barcode</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>EAN/UPC barcode number for scanner</td>
                            </tr>
                            <tr>
                                <td><code>category</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Category name (auto-created if new)</td>
                            </tr>
                            <tr>
                                <td><code>unit</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Unit code (e.g. <code>pcs</code>, <code>kg</code>, <code>box</code>)</td>
                            </tr>
                            <tr>
                                <td><code>cost_price</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Purchase cost price (e.g. <code>25000</code>)</td>
                            </tr>
                            <tr>
                                <td><code>selling_price</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Retail selling price (e.g. <code>35000</code>)</td>
                            </tr>
                            <tr>
                                <td><code>wholesale_price</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Grosir price for volume buyers</td>
                            </tr>
                            <tr>
                                <td><code>stock_quantity</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Initial inventory stock quantity (default 0)</td>
                            </tr>
                            <tr>
                                <td><code>min_stock_alert</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Low stock threshold (default 5)</td>
                            </tr>
                            <tr>
                                <td><code>description</code></td>
                                <td><span class="badge bg-secondary">Optional</span></td>
                                <td>Product specifications or details</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <a href="{{ route('products.import.template') }}" class="btn btn-sm btn-outline-success w-100">
                        <i class="fas fa-download me-1"></i> Download Pre-Formatted Template
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
