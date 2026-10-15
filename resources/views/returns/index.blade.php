@extends('layouts.app')
@section('title', 'Sales Returns & Refunds')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Sales Returns &amp; Refunds</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Returns</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('returns.create') }}" class="btn btn-warning text-dark">
        <i class="fas fa-rotate-left me-1"></i> New Return Request
    </a>
</div>

{{-- Filter Card --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('returns.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Return # or Invoice #" value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request('search'))
                    <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Returns Table --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold"><i class="fas fa-rotate-left me-2 text-warning"></i>Returns List</h6>
        <span class="badge bg-light text-dark border">{{ $returns->total() }} Records</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
            <thead class="table-light">
                <tr>
                    <th>Return #</th>
                    <th>Date</th>
                    <th>Original Invoice</th>
                    <th>Processed By</th>
                    <th>Refund Method</th>
                    <th>Status</th>
                    <th class="text-end">Total Refund</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $return)
                    <tr>
                        <td>
                            <a href="{{ route('returns.show', $return->id) }}" class="fw-bold text-dark text-decoration-none">
                                {{ $return->return_number }}
                            </a>
                        </td>
                        <td>{{ $return->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            @if($return->sale)
                                <a href="{{ route('sales.show', $return->sale_id) }}" class="text-primary fw-semibold text-decoration-none">
                                    {{ $return->sale->invoice_number }}
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $return->user?->name ?? 'Staff' }}</td>
                        <td><span class="badge bg-light text-dark border text-uppercase">{{ $return->refund_method }}</span></td>
                        <td><span class="badge bg-success-subtle text-success">{{ ucfirst($return->status) }}</span></td>
                        <td class="text-end fw-bold text-danger">Rp {{ number_format($return->total_refund, 0, ',', '.') }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('returns.show', $return->id) }}" class="btn btn-outline-info" title="View details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(auth()->check() && auth()->user()->isAdmin())
                                    <button type="button" class="btn btn-outline-danger" title="Delete" onclick="deleteReturn({{ $return->id }}, '{{ $return->return_number }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                            @if(auth()->check() && auth()->user()->isAdmin())
                                <form id="delete-return-{{ $return->id }}" action="{{ route('returns.destroy', $return->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="fas fa-rotate-left fa-3x mb-3 d-block opacity-25"></i>
                            No return records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($returns->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $returns->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function deleteReturn(id, ref) {
        Swal.fire({
            title: 'Delete Return Record?',
            text: `Are you sure you want to delete return record "${ref}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-return-' + id).submit();
            }
        });
    }
</script>
@endsection
