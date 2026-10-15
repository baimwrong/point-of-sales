@extends('layouts.app')
@section('title', 'Edit Purchase: ' . $purchase->reference_number)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Edit Purchase Order: {{ $purchase->reference_number }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}" class="text-decoration-none">Purchases</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-outline-info">
            <i class="fas fa-eye me-1"></i> View Order
        </a>
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="fas fa-edit me-2 text-primary"></i>Update Order Status & Notes</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('purchases.update', $purchase->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Supplier</label>
                        <input type="text" class="form-control" value="{{ $purchase->supplier?->name ?? 'Walk-in / Direct' }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Total Amount</label>
                        <input type="text" class="form-control" value="Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="pending" {{ old('status', $purchase->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="received" {{ old('status', $purchase->status) === 'received' ? 'selected' : '' }}>Received</option>
                            <option value="partial" {{ old('status', $purchase->status) === 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="cancelled" {{ old('status', $purchase->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes</label>
                        <textarea name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $purchase->notes) }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
