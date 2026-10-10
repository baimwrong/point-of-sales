@props([
    'title',
    'value',
    'icon',
    'color' => 'primary',
    'subtitle' => null,
    'link' => null,
    'linkText' => 'View details'
])

<div class="card stat-card h-100 shadow-sm border-0">
    <div class="card-body d-flex align-items-center gap-3 p-4">
        <div class="stat-icon bg-{{ $color }}-subtle text-{{ $color }} rounded-3 d-flex align-items-center justify-content-center p-3" style="width: 52px; height: 52px; font-size: 1.3rem;">
            <i class="{{ $icon }}"></i>
        </div>
        <div class="flex-fill">
            <div class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: .75rem; letter-spacing: .05em;">
                {{ $title }}
            </div>
            <div class="fw-bold fs-4 text-dark lh-1">
                {{ $value }}
            </div>
            @if ($subtitle)
                <div class="text-muted mt-1 small">{{ $subtitle }}</div>
            @elseif ($link)
                <div class="mt-1 small">
                    <a href="{{ $link }}" class="text-{{ $color }} text-decoration-none">{{ $linkText }} &rarr;</a>
                </div>
            @endif
        </div>
    </div>
</div>
