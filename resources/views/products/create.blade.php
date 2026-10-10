@extends('layouts.app')
@section('title', 'Add Product')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Add New Product</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-decoration-none">Products</a></li>
                <li class="breadcrumb-item active" aria-current="page">Add Product</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Products
    </a>
</div>

@include('components.alert')

<form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-4">
        {{-- Left: General Info --}}
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-box me-2 text-primary"></i>Basic Information</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Kopi Arabika 250g" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">SKU (Stock Keeping Unit) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" id="sku" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku') }}" placeholder="PRD-0001" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="generateSku()">
                                    <i class="fas fa-random"></i> Auto
                                </button>
                            </div>
                            @error('sku')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Barcode / EAN</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-barcode text-muted"></i></span>
                                <input type="text" name="barcode" class="form-control @error('barcode') is-invalid @enderror" value="{{ old('barcode') }}" placeholder="e.g. 899123456789">
                            </div>
                            @error('barcode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category</label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Unit / Satuan</label>
                            <select name="unit_id" class="form-select @error('unit_id') is-invalid @enderror">
                                <option value="">-- Select Unit --</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }} ({{ $unit->abbreviation }})
                                    </option>
                                @endforeach
                            </select>
                            @error('unit_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror" placeholder="Product details, specs, notes...">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            {{-- Pricing Card --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-tags me-2 text-primary"></i>Pricing & Tax</h6>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cost Price (Harga Modal) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">Rp</span>
                                <input type="number" step="0.01" name="cost_price" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price', 0) }}" required>
                            </div>
                            @error('cost_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Selling Price (Harga Jual) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">Rp</span>
                                <input type="number" step="0.01" name="selling_price" class="form-control @error('selling_price') is-invalid @enderror" value="{{ old('selling_price', 0) }}" required>
                            </div>
                            @error('selling_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Wholesale Price (Grosir)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">Rp</span>
                                <input type="number" step="0.01" name="wholesale_price" class="form-control @error('wholesale_price') is-invalid @enderror" value="{{ old('wholesale_price') }}" placeholder="Optional">
                            </div>
                            @error('wholesale_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tax Rate Override (%)</label>
                            <input type="number" step="0.01" name="tax_rate" class="form-control @error('tax_rate') is-invalid @enderror" value="{{ old('tax_rate', 0) }}">
                            <div class="form-text small text-muted">Set 0 to inherit store tax.</div>
                            @error('tax_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Default Discount</label>
                            <div class="input-group">
                                <select name="discount_type" class="form-select" style="max-width: 90px;">
                                    <option value="none" {{ old('discount_type') == 'none' ? 'selected' : '' }}>None</option>
                                    <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Rp</option>
                                    <option value="percent" {{ old('discount_type') == 'percent' ? 'selected' : '' }}>%</option>
                                </select>
                                <input type="number" step="0.01" name="discount_value" class="form-control" value="{{ old('discount_value', 0) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Image, Stock & Status --}}
        <div class="col-lg-4">
            {{-- Image Upload --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-image me-2 text-primary"></i>Product Image</h6>
                </div>
                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        <img id="imagePreview" src="{{ asset('images/placeholder-product.png') }}" class="img-thumbnail rounded-3" style="max-height: 180px; width: 100%; object-fit: contain; background: #f8fafc;" alt="Product preview">
                    </div>
                    <input type="file" name="image" id="imageInput" class="form-control form-control-sm @error('image') is-invalid @enderror" accept="image/*" onchange="previewImage(event)">
                    <div class="form-text small text-muted mt-1">PNG, JPG, or WEBP up to 2MB.</div>
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- Stock Control --}}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-cubes me-2 text-primary"></i>Inventory & Stock</h6>
                </div>
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="track_stock" id="trackStock" value="1" {{ old('track_stock', true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="trackStock">Track Stock Quantity</label>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Initial Stock Quantity</label>
                        <input type="number" name="stock_quantity" class="form-control @error('stock_quantity') is-invalid @enderror" value="{{ old('stock_quantity', 0) }}" min="0">
                        @error('stock_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Low Stock Alert Level</label>
                        <input type="number" name="min_stock_alert" class="form-control @error('min_stock_alert') is-invalid @enderror" value="{{ old('min_stock_alert', 5) }}" min="0">
                        <div class="form-text small text-muted">Warn when stock falls at or below this level.</div>
                        @error('min_stock_alert')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <hr class="my-3">

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="isActive">Product is Active (Visible in POS)</label>
                    </div>
                </div>
            </div>

            {{-- Save Action --}}
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary py-2 fw-semibold">
                    <i class="fas fa-save me-1"></i> Save Product
                </button>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary py-2">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    function generateSku() {
        const rand = Math.floor(1000 + Math.random() * 9000);
        document.getElementById('sku').value = 'SKU-' + Date.now().toString().slice(-4) + '-' + rand;
    }

    function previewImage(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('imagePreview').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
