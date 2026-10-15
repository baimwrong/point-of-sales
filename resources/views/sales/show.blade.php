@extends('layouts.app')
@section('title', 'Sale: ' . $sale->invoice_number)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">{{ $sale->invoice_number }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sales.index') }}" class="text-decoration-none">Sales</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $sale->invoice_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pos.receipt', $sale->id) }}" target="_blank" class="btn btn-outline-secondary">
            <i class="fas fa-print me-1"></i> Print Receipt
        </a>
        @if($sale->status === 'completed')
            <a href="{{ route('returns.create', ['sale_id' => $sale->id]) }}" class="btn btn-warning text-dark">
                <i class="fas fa-rotate-left me-1"></i> Return / Refund
            </a>
        @endif
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Left: Items and Summary --}}
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold"><i class="fas fa-box-open me-2 text-primary"></i>Items Ordered</h6>
                <span class="badge bg-light text-dark border">{{ $sale->saleItems->count() }} line items</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Item Name</th>
                            <th>SKU</th>
                            <th class="text-end">Price</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->saleItems as $item)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $item->product_name }}</div>
                                    @if($item->barcode)
                                        <div class="text-muted small"><i class="fas fa-barcode me-1"></i>{{ $item->barcode }}</div>
                                    @endif
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $item->product_sku }}</span></td>
                                <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                <td class="text-end text-danger">
                                    {{ $item->discount_amount > 0 ? '-Rp ' . number_format($item->discount_amount, 0, ',', '.') : '-' }}
                                </td>
                                <td class="text-end fw-bold">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Returns associated with this sale --}}
        @if($sale->saleReturns->count() > 0)
            <div class="card shadow-sm border-0 mb-4 border-warning">
                <div class="card-header bg-warning-subtle py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-rotate-left me-2 text-warning"></i>Refund &amp; Return History</h6>
                    <span class="badge bg-warning text-dark">{{ $sale->saleReturns->count() }} Return(s)</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0" style="font-size: .88rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Return #</th>
                                <th>Date</th>
                                <th>Refund Total</th>
                                <th>Processed By</th>
                                <th class="text-end">View</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->saleReturns as $ret)
                                <tr>
                                    <td><strong>{{ $ret->return_number }}</strong></td>
                                    <td>{{ $ret->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-danger fw-semibold">Rp {{ number_format($ret->total_refund, 0, ',', '.') }}</td>
                                    <td>{{ $ret->user?->name ?? 'Staff' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('returns.show', $ret->id) }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- Right: Payment & Info --}}
    <div class="col-lg-4">
        {{-- Invoice Overview --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="fas fa-file-invoice me-2 text-primary"></i>Invoice Details</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Status</span>
                    <span>{!! $sale->status_badge !!}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Date & Time</span>
                    <strong class="text-dark small">{{ $sale->created_at->format('d M Y, H:i:s') }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Cashier</span>
                    <strong class="text-dark small">{{ $sale->user?->name ?? '-' }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Customer</span>
                    <strong class="text-dark small">{{ $sale->customer?->name ?? 'Walk-in Customer' }}</strong>
                </div>
                @if($sale->notes)
                    <div class="mt-3 p-2 bg-light rounded small">
                        <strong>Notes:</strong> {{ $sale->notes }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Financial Breakdown --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="fas fa-coins me-2 text-primary"></i>Financial Summary</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal</span>
                    <span>Rp {{ number_format($sale->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($sale->discount_amount > 0)
                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span>Discount ({{ $sale->discount_type }})</span>
                        <span>-Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                @if($sale->tax_amount > 0)
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Tax ({{ $sale->tax_rate }}%)</span>
                        <span>Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <hr class="my-2">
                <div class="d-flex justify-content-between fs-5 fw-bold mb-3">
                    <span>Total Amount</span>
                    <span class="text-primary">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                </div>

                <div class="p-3 bg-light rounded-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Payment Method</span>
                        <strong class="text-uppercase">{{ $sale->payment_method }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Paid Amount</span>
                        <strong>Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Change</span>
                        <strong class="text-success">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
