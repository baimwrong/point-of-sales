@extends('layouts.app')
@section('title', 'Purchase Order: ' . $purchase->reference_number)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">{{ $purchase->reference_number }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}" class="text-decoration-none">Purchases</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $purchase->reference_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        @if($purchase->status === 'pending')
            <form action="{{ route('purchases.receive', $purchase->id) }}" method="POST" onsubmit="return confirm('Confirm receipt of goods? This will update current stock quantities.');">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-check-circle me-1"></i> Receive Order (Add to Stock)
                </button>
            </form>
            <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
        @endif
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    {{-- Left: Items List --}}
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold"><i class="fas fa-boxes me-2 text-primary"></i>Ordered Items</h6>
                <span class="badge bg-light text-dark border">{{ $purchase->purchaseItems->count() }} Items</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Item Name</th>
                            <th>SKU</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-end">Unit Cost</th>
                            <th class="text-end">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->purchaseItems as $item)
                            <tr>
                                <td>
                                    @if($item->product)
                                        <a href="{{ route('products.show', $item->product_id) }}" class="fw-semibold text-dark text-decoration-none">
                                            {{ $item->product_name }}
                                        </a>
                                    @else
                                        <span class="fw-semibold text-dark">{{ $item->product_name }}</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $item->product_sku }}</span></td>
                                <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                <td class="text-end">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold">Rp {{ number_format($item->total_cost, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end">Total Procurement:</th>
                            <th class="text-end fw-bold text-primary fs-6">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Right: Order Metadata --}}
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="fas fa-file-invoice me-2 text-primary"></i>Order Information</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-muted small">Order Status</span>
                    <span>{!! $purchase->status_badge !!}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-muted small">Date Created</span>
                    <strong class="text-dark small">{{ $purchase->created_at->format('d M Y, H:i') }}</strong>
                </div>
                @if($purchase->received_at)
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-muted small">Date Received</span>
                        <strong class="text-success small">{{ $purchase->received_at->format('d M Y, H:i') }}</strong>
                    </div>
                @endif
                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-muted small">Created By</span>
                    <strong class="text-dark small">{{ $purchase->user?->name ?? 'Staff' }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-muted small">Supplier</span>
                    <strong class="text-dark small">
                        @if($purchase->supplier)
                            <a href="{{ route('suppliers.show', $purchase->supplier_id) }}" class="text-primary text-decoration-none">
                                {{ $purchase->supplier->name }}
                            </a>
                        @else
                            Direct Vendor
                        @endif
                    </strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Paid Amount</span>
                    <strong class="text-dark small">Rp {{ number_format($purchase->paid_amount, 0, ',', '.') }}</strong>
                </div>
                @if($purchase->notes)
                    <div class="mt-3 p-3 bg-light rounded-3 small">
                        <strong>Notes:</strong> {{ $purchase->notes }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
