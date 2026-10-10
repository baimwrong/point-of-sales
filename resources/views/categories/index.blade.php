@extends('layouts.app')

@section('title', 'Categories')
@section('page-title', 'Categories')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Categories</li>
@endsection

@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="mb-0 fw-bold">Categories</h4>
        <p class="text-muted mb-0 small">Organize your products into categories</p>
    </div>
    <a href="{{ route('categories.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Category
    </a>
</div>

<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('categories.index') }}" class="row g-2 align-items-end">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Search categories…" value="{{ request('search') }}">
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
                    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between py-3">
        <span class="fw-semibold"><i class="fas fa-tags me-2 text-primary"></i>Category List</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:50px">#</th>
                    <th style="width:60px">Image</th>
                    <th>Category Name</th>
                    <th>Parent Category</th>
                    <th>Products</th>
                    <th>Sort Order</th>
                    <th>Status</th>
                    <th class="text-center" style="width:120px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories ?? [] as $category)
                <tr>
                    <td class="text-muted small">{{ $loop->iteration }}</td>
                    <td>
                        @if($category->image)
                        <img src="{{ asset('storage/'.$category->image) }}" alt="{{ $category->name }}"
                             style="width:40px;height:40px;object-fit:cover;border-radius:8px;border:1px solid #dee2e6">
                        @else
                        <div style="width:40px;height:40px;background:#f0f2f5;border-radius:8px;display:flex;align-items:center;justify-content:center">
                            <i class="fas fa-tag text-muted small"></i>
                        </div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $category->name }}</div>
                        @if($category->description)
                        <div class="text-muted small text-truncate" style="max-width:200px">{{ $category->description }}</div>
                        @endif
                    </td>
                    <td>
                        @if($category->parent)
                            <span class="badge bg-secondary">{{ $category->parent->name }}</span>
                        @else
                            <span class="text-muted small">— Root</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border">
                            <i class="fas fa-box me-1"></i>{{ $category->products_count ?? 0 }}
                        </span>
                    </td>
                    <td class="text-muted">{{ $category->sort_order ?? 0 }}</td>
                    <td>
                        @if($category->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('categories.edit', $category) }}" class="btn btn-outline-primary" title="Edit">
                                <i class="fas fa-pencil"></i>
                            </a>
                            <button type="button" class="btn btn-outline-danger" title="Delete"
                                    onclick="confirmDelete('delete-cat-{{ $category->id }}', '{{ $category->name }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        <form id="delete-cat-{{ $category->id }}" action="{{ route('categories.destroy', $category) }}" method="POST" class="d-none">
                            @csrf @method('DELETE')
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="fas fa-tags fa-3x mb-3 d-block opacity-25"></i>
                        No categories found.
                        <a href="{{ route('categories.create') }}" class="d-block mt-2 text-primary">Add your first category →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($categories) && method_exists($categories, 'links'))
    <div class="card-footer bg-white py-3">
        {{ $categories->withQueryString()->links() }}
    </div>
    @endif
</div>

@endsection
