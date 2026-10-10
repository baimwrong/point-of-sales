@extends('layouts.app')
@section('title', 'Purchase Orders')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Purchase Orders (Pengadaan Barang)</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Purchases</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> New Purchase Order
    </a>
</div>

@include('components.alert')

{{-- Filter Card --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('purchases.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Reference # or Supplier" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-semibold mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
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
                <button type="submit" class="btn btn-secondary btn-sm w-100">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
            </div>
            <div class="col-md-1">
                @if(request()->anyFilled(['search', 'status', 'date_from', 'date_to']))
                    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary btn-sm w-100">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Purchases Table --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold"><i class="fas fa-truck-ramp-box me-2 text-primary"></i>Purchase Orders List</h6>
        <span class="badge bg-light text-dark border">{{ $purchases->total() }} Orders</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
            <thead class="table-light">
                <tr>
                    <th>Reference #</th>
                    <th>Date</th>
                    <th>Supplier</th>
                    <th>Created By</th>
                    <th>Status</th>
                    <th class="text-end">Total Amount</th>
                    <th class="text-end">Actions</th>
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
                        <td>
                            @if($purchase->supplier)
                                <a href="{{ route('suppliers.show', $purchase->supplier_id) }}" class="text-dark fw-semibold text-decoration-none">
                                    {{ $purchase->supplier->name }}
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $purchase->user?->name ?? 'Staff' }}</td>
                        <td>{!! $purchase->status_badge !!}</td>
                        <td class="text-end fw-bold">Rp {{ number_format($purchase->total_amount, 0, ',', '.') }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('purchases.show', $purchase->id) }}" class="btn btn-outline-info" title="View details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($purchase->status === 'pending')
                                    <a href="{{ route('purchases.edit', $purchase->id) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('purchases.receive', $purchase->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark as received and add stock to inventory?');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" title="Mark as Received">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                                @if(auth()->check() && auth()->user()->isAdmin())
                                    <button type="button" class="btn btn-outline-danger" title="Delete" onclick="deletePurchase({{ $purchase->id }}, '{{ $purchase->reference_number }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                            @if(auth()->check() && auth()->user()->isAdmin())
                                <form id="delete-purchase-{{ $purchase->id }}" action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fas fa-boxes-packing fa-3x mb-3 d-block opacity-25"></i>
                            No purchase orders found. Click <strong>New Purchase Order</strong> to record inventory procurement.
                        </td>
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
@endsection

@section('scripts')
<script>
    function deletePurchase(id, ref) {
        Swal.fire({
            title: 'Delete Purchase Order?',
            text: `Are you sure you want to delete purchase order "${ref}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-purchase-' + id).submit();
            }
        });
    }
</script>
@endsection
