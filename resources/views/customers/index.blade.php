@extends('layouts.app')
@section('title', 'Customers')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Customer Management</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Customers</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('customers.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus me-1"></i> Add Customer
    </a>
</div>

@include('components.alert')

{{-- Filter Card --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('customers.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search name, phone, email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request('search'))
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Customers Table Card --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold"><i class="fas fa-users me-2 text-primary"></i>All Customers</h6>
        <span class="badge bg-light text-dark border">{{ $customers->total() }} Customers</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
            <thead class="table-light">
                <tr>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>City</th>
                    <th class="text-center">Loyalty Points</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: .85rem;">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <a href="{{ route('customers.show', $customer->id) }}" class="fw-semibold text-dark text-decoration-none">
                                        {{ $customer->name }}
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td>{{ $customer->phone ?: '-' }}</td>
                        <td>{{ $customer->email ?: '-' }}</td>
                        <td>{{ $customer->city ?: '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-warning text-dark px-2 py-1">
                                <i class="fas fa-star me-1 text-primary"></i> {{ number_format($customer->loyalty_points, 0) }} pts
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-outline-info" title="View details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-outline-danger" title="Delete" onclick="deleteCustomer({{ $customer->id }}, '{{ addslashes($customer->name) }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                            <form id="delete-form-{{ $customer->id }}" action="{{ route('customers.destroy', $customer->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="fas fa-users fa-3x mb-3 d-block opacity-25"></i>
                            No customers found. Click <strong>Add Customer</strong> to create one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $customers->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function deleteCustomer(id, name) {
        Swal.fire({
            title: 'Delete Customer?',
            text: `Are you sure you want to delete "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endsection
