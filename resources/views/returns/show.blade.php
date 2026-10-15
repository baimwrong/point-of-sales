@extends('layouts.app')
@section('title', 'Return: ' . $return->return_number)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">{{ $return->return_number }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('returns.index') }}" class="text-decoration-none">Returns</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $return->return_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        @if($return->sale)
            <a href="{{ route('sales.show', $return->sale_id) }}" class="btn btn-outline-info">
                <i class="fas fa-file-invoice me-1"></i> Original Sale #{{ $return->sale->invoice_number }}
            </a>
        @endif
        <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold"><i class="fas fa-box-open me-2 text-warning"></i>Returned Items</h6>
                <span class="badge bg-light text-dark border">{{ $return->returnItems->count() }} Line Items</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Product Name</th>
                            <th class="text-center">Returned Qty</th>
                            <th class="text-end">Unit Price</th>
                            <th class="text-end">Refund Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($return->returnItems as $item)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $item->product_name }}</div>
                                </td>
                                <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold text-danger">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">Total Refunded:</th>
                            <th class="text-end fw-bold text-danger fs-6">Rp {{ number_format($return->total_refund, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="fas fa-circle-info me-2 text-primary"></i>Return Information</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-muted small">Return Status</span>
                    <span class="badge bg-success-subtle text-success">{{ ucfirst($return->status) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-muted small">Date & Time</span>
                    <strong class="text-dark small">{{ $return->created_at->format('d M Y, H:i') }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-muted small">Processed By</span>
                    <strong class="text-dark small">{{ $return->user?->name ?? 'Staff' }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                    <span class="text-muted small">Refund Method</span>
                    <span class="badge bg-light text-dark border text-uppercase">{{ $return->refund_method }}</span>
                </div>
                <div class="mb-2 pb-2 border-bottom">
                    <span class="text-muted small d-block">Reason</span>
                    <strong class="text-dark">{{ $return->reason ?: '-' }}</strong>
                </div>
                @if($return->notes)
                    <div class="mt-2 p-2 bg-light rounded small">
                        <strong>Notes:</strong> {{ $return->notes }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
