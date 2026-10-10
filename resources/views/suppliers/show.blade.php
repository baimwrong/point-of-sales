@extends('layouts.app')
@section('title', 'Supplier: ' . $supplier->name)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">{{ $supplier->name }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('suppliers.index') }}" class="text-decoration-none">Suppliers</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $supplier->name }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('purchases.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-success">
            <i class="fas fa-plus me-1"></i> New Purchase Order
        </a>
        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit
        </a>
        <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

@include('components.alert')

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="fas fa-id-card me-2 text-primary"></i>Profile Info</h6>
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="text-muted small">Company</label>
                    <div class="fw-semibold text-dark">{{ $supplier->company ?: '-' }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Email</label>
                    <div class="fw-semibold text-dark">{{ $supplier->email ?: '-' }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Phone</label>
                    <div class="fw-semibold text-dark">{{ $supplier->phone ?: '-' }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">City</label>
                    <div class="fw-semibold text-dark">{{ $supplier->city ?: '-' }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Tax Number (NPWP)</label>
                    <div class="fw-semibold text-dark">{{ $supplier->tax_number ?: '-' }}</div>
                </div>
                <div class="mb-0">
                    <label class="text-muted small">Address</label>
                    <div class="text-dark">{{ $supplier->address ?: '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold"><i class="fas fa-cart-arrow-down me-2 text-primary"></i>Purchase History</h6>
                <span class="badge bg-light text-dark border">{{ $purchases->total() }} Orders</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Reference #</th>
                            <th>Date</th>
                            <th>Created By</th>
                            <th>Status</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                            <tr>
                                <td>
                                    <a href="{{ route('purchases.show', $purchase->id) }}" class="fw-bold text-primary text-decoration-none">
                                        {{ $purchase->reference_number }}
                                    </a>
                                </td>
                                <td>{{ $purchase->created_at->format('d/m/Y') }}</td>
                                <td>{{ $purchase->user?->name ?? 'Staff' }}</td>
                                <td>{!! $purchase->status_badge !!}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No purchase orders found for this supplier.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($purchases->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
