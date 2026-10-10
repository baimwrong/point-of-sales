@props(['title', 'subtitle' => null, 'breadcrumbs' => []])

<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="mb-1 fw-bold text-dark">{{ $title }}</h4>
        @if ($subtitle)
            <p class="text-muted mb-0 small">{{ $subtitle }}</p>
        @endif
        @if (!empty($breadcrumbs))
            <nav aria-label="breadcrumb" class="mt-1">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                    @foreach ($breadcrumbs as $label => $url)
                        @if ($loop->last)
                            <li class="breadcrumb-item active" aria-current="page">{{ $label }}</li>
                        @else
                            <li class="breadcrumb-item"><a href="{{ $url }}" class="text-decoration-none">{{ $label }}</a></li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif
    </div>
    @if (isset($actions))
        <div class="d-flex align-items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
