@extends('layouts.app')
@section('title', 'Product Units')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">Units of Measurement (Satuan)</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Units</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('units.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Add Unit
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold"><i class="fas fa-ruler me-2 text-primary"></i>All Units</h6>
        <span class="badge bg-light text-dark border">{{ $units->total() }} Units</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
            <thead class="table-light">
                <tr>
                    <th style="width: 80px;">#</th>
                    <th>Unit Name</th>
                    <th>Abbreviation / Symbol</th>
                    <th class="text-center">Products Using</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($units as $index => $unit)
                    <tr>
                        <td>{{ $units->firstItem() + $index }}</td>
                        <td class="fw-semibold text-dark">{{ $unit->name }}</td>
                        <td><span class="badge bg-light text-dark border px-2 py-1">{{ $unit->abbreviation }}</span></td>
                        <td class="text-center">
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ $unit->products_count }} products</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('units.edit', $unit->id) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($unit->products_count == 0)
                                    <button type="button" class="btn btn-outline-danger" title="Delete" onclick="deleteUnit({{ $unit->id }}, '{{ addslashes($unit->name) }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-outline-secondary disabled" title="Cannot delete: in use">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                            <form id="delete-form-{{ $unit->id }}" action="{{ route('units.destroy', $unit->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-5">
                            No units defined yet. Click <strong>Add Unit</strong> to create one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($units->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $units->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function deleteUnit(id, name) {
        Swal.fire({
            title: 'Delete Unit?',
            text: `Are you sure you want to delete unit "${name}"?`,
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
