@extends('layouts.app')

@section('title', 'Suppliers')
@section('page-title', 'Suppliers')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Suppliers</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Suppliers</h4>
        <p class="text-muted mb-0 small">Manage your supplier relationships</p>
    </div>
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Supplier
    </a>
</div>

<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('suppliers.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Name, company, email…" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="is_active" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-auto">
                <div class="d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between py-3">
        <span class="fw-semibold"><i class="fas fa-truck me-2 text-primary"></i>Supplier List</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers ?? [] as $supplier)
                <tr>
                    <td class="text-muted small">{{ $loop->iteration }}</td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.85rem;flex-shrink:0">
                                {{ strtoupper(substr($supplier->name, 0, 1)) }}
                            </div>
                            <span class="fw-semibold">{{ $supplier->name }}</span>
                        </div>
                    </td>
                    <td>{{ $supplier->company ?? '—' }}</td>
                    <td><a href="mailto:{{ $supplier->email }}" class="text-decoration-none">{{ $supplier->email ?? '—' }}</a></td>
                    <td>{{ $supplier->phone ?? '—' }}</td>
                    <td>
                        @php $balance = $supplier->balance ?? 0; @endphp
                        <span class="{{ $balance < 0 ? 'text-danger' : 'text-success' }} fw-semibold">
                            Rp {{ number_format(abs($balance), 0, ',', '.') }}
                            @if($balance < 0) <i class="fas fa-arrow-down small"></i> @elseif($balance > 0) <i class="fas fa-arrow-up small"></i> @endif
                        </span>
                    </td>
                    <td>
                        @if($supplier->is_active ?? true)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('suppliers.show', $supplier->id) }}" class="btn btn-outline-info" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-outline-primary" title="Edit">
                                <i class="fas fa-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-outline-danger" title="Delete"
                                    onclick="confirmDelete('delete-sup-{{ $supplier->id }}', '{{ addslashes($supplier->name) }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <form id="delete-sup-{{ $supplier->id }}" action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="d-none">
                            @csrf @method('DELETE')
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="fas fa-truck fa-3x mb-3 d-block opacity-25"></i>
                        No suppliers found.
                        <a href="{{ route('suppliers.create') }}" class="d-block mt-2 text-primary">Add your first supplier →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($suppliers) && method_exists($suppliers, 'links'))
    <div class="card-footer bg-white py-3">
        {{ $suppliers->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
