@extends('layouts.app')
@section('title', 'Sales Transactions')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Sales Transactions</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Sales History</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sales.export', request()->all()) }}" class="btn btn-outline-success">
            <i class="fas fa-file-excel me-1"></i> Export CSV
        </a>
        <a href="{{ route('pos.index') }}" class="btn btn-primary">
            <i class="fas fa-cash-register me-1"></i> Open POS
        </a>
    </div>
</div>

{{-- Filter Card --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('sales.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Invoice # or customer name" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Payment</label>
                <select name="payment_method" class="form-select form-select-sm">
                    <option value="">All Payments</option>
                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="card" {{ request('payment_method') === 'card' ? 'selected' : '' }}>Card</option>
                    <option value="transfer" {{ request('payment_method') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="qris" {{ request('payment_method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                    <option value="split" {{ request('payment_method') === 'split' ? 'selected' : '' }}>Split</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                    <option value="partial_refund" {{ request('status') === 'partial_refund' ? 'selected' : '' }}>Partial Refund</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-secondary btn-sm w-100">
                    <i class="fas fa-filter"></i>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Sales Table --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold"><i class="fas fa-receipt me-2 text-primary"></i>Sales Records</h6>
        <span class="badge bg-light text-dark border">{{ $sales->total() }} Transactions</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
            <thead class="table-light">
                <tr>
                    <th>Invoice #</th>
                    <th>Date & Time</th>
                    <th>Customer</th>
                    <th>Cashier</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th class="text-end">Total Amount</th>
                    <th class="text-end">Actions</th>
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
                        <td>
                            @if($sale->customer)
                                <a href="{{ route('customers.show', $sale->customer_id) }}" class="text-dark text-decoration-none fw-semibold">
                                    {{ $sale->customer->name }}
                                </a>
                            @else
                                <span class="text-muted">Walk-in</span>
                            @endif
                        </td>
                        <td>{{ $sale->user?->name ?? 'Staff' }}</td>
                        <td>{!! $sale->payment_method_badge !!}</td>
                        <td>{!! $sale->status_badge !!}</td>
                        <td class="text-end fw-bold">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-outline-info" title="View details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('pos.receipt', $sale->id) }}" target="_blank" class="btn btn-outline-secondary" title="Print receipt">
                                    <i class="fas fa-print"></i>
                                </a>
                                @if(auth()->check() && auth()->user()->isAdmin())
                                    <button type="button" class="btn btn-outline-danger" title="Delete" onclick="deleteSale({{ $sale->id }}, '{{ $sale->invoice_number }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                            @if(auth()->check() && auth()->user()->isAdmin())
                                <form id="delete-sale-{{ $sale->id }}" action="{{ route('sales.destroy', $sale->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-3x mb-3 d-block opacity-25"></i>
                            No sales transactions found matching filters.
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
@endsection

@section('scripts')
<script>
    function deleteSale(id, invoice) {
        Swal.fire({
            title: 'Delete Sale Record?',
            text: `Are you sure you want to delete transaction "${invoice}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-sale-' + id).submit();
            }
        });
    }
</script>
@endsection
