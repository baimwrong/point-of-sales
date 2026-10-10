@extends('layouts.app')
@section('title', 'Customer: ' . $customer->name)

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">{{ $customer->name }}</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('customers.index') }}" class="text-decoration-none">Customers</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $customer->name }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit Profile
        </a>
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

@include('components.alert')

<div class="row g-4 mb-4">
    {{-- Left column: Customer Profile & Loyalty Card --}}
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4 text-center p-4">
            <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-3" style="width: 72px; height: 72px; font-size: 1.8rem;">
                {{ strtoupper(substr($customer->name, 0, 1)) }}
            </div>
            <h5 class="fw-bold text-dark mb-1">{{ $customer->name }}</h5>
            <div class="text-muted small mb-3">{{ $customer->city ?: 'No city specified' }}</div>

            <div class="p-3 bg-warning-subtle text-dark rounded-3 mb-3 text-center border border-warning-subtle">
                <div class="small fw-semibold text-uppercase text-muted" style="letter-spacing: .05em;">Loyalty Points</div>
                <div class="fs-3 fw-bold text-dark">
                    <i class="fas fa-star text-warning me-1"></i> {{ number_format($customer->loyalty_points, 0) }}
                </div>
            </div>

            <div class="text-start">
                <div class="mb-2 pb-2 border-bottom">
                    <span class="text-muted small d-block">Phone / WhatsApp</span>
                    <strong class="text-dark">{{ $customer->phone ?: '-' }}</strong>
                </div>
                <div class="mb-2 pb-2 border-bottom">
                    <span class="text-muted small d-block">Email</span>
                    <strong class="text-dark">{{ $customer->email ?: '-' }}</strong>
                </div>
                <div class="mb-2 pb-2 border-bottom">
                    <span class="text-muted small d-block">Address</span>
                    <span class="text-dark small">{{ $customer->address ?: '-' }}</span>
                </div>
                @if($customer->notes)
                    <div class="mb-0">
                        <span class="text-muted small d-block">Notes</span>
                        <span class="text-dark small">{{ $customer->notes }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="mb-0 fw-bold"><i class="fas fa-chart-pie me-2 text-primary"></i>Spending Summary</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Orders</span>
                    <strong>{{ $sales->total() }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Lifetime Spend</span>
                    <strong class="text-success fs-5">Rp {{ number_format($totalSpent, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Right column: Transaction History --}}
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="mb-0 fw-bold"><i class="fas fa-receipt me-2 text-primary"></i>Purchase History</h6>
                <span class="badge bg-light text-dark border">{{ $sales->total() }} Invoices</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-end">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr>
                                <td>
                                    <a href="{{ route('sales.show', $sale->id) }}" class="fw-bold text-primary text-decoration-none">
                                        {{ $sale->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                                <td>{!! $sale->payment_method_badge !!}</td>
                                <td>{!! $sale->status_badge !!}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('pos.receipt', $sale->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="View receipt">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">
                                    <i class="fas fa-receipt fa-2x mb-2 d-block opacity-25"></i>
                                    No purchase transactions recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($sales->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $sales->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
