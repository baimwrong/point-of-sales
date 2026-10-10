@extends('layouts.app')
@section('title', 'Users Management')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">User Management</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Users</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-primary">
        <i class="fas fa-user-plus me-1"></i> Add User
    </a>
</div>

@include('components.alert')

{{-- Filter Card --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search user name or email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request('search'))
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Users Table --}}
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold"><i class="fas fa-users-gear me-2 text-primary"></i>System Users &amp; Cashiers</h6>
        <span class="badge bg-light text-dark border">{{ $users->total() }} Accounts</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: .88rem;">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th class="text-center">Status</th>
                    <th>Created At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: .85rem;">
                                    {{ $user->initials }}
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                    @if($user->phone)
                                        <div class="text-muted small">{{ $user->phone }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge {{ $user->isAdmin() ? 'bg-danger' : ($user->isManager() ? 'bg-primary' : 'bg-info text-dark') }} px-2 py-1">
                                {{ $user->role?->display_name ?? 'User' }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($user->is_active)
                                <span class="badge bg-success-subtle text-success">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <form action="{{ route('users.toggleActive', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-{{ $user->is_active ? 'warning' : 'success' }}" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}" {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                        <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                    </button>
                                </form>
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                    <button type="button" class="btn btn-outline-danger" title="Delete" onclick="deleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                            @if($user->id !== auth()->id())
                                <form id="delete-user-{{ $user->id }}" action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            No users found matching query.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function deleteUser(id, name) {
        Swal.fire({
            title: 'Delete User Account?',
            text: `Are you sure you want to delete user "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-user-' + id).submit();
            }
        });
    }
</script>
@endsection
