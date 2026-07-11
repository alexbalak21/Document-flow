{{--
    x-ui.page-header
    Props:
      $title      – heading text
      $backRoute  – (optional) URL for back arrow
      $subtitle   – (optional) small muted text below title
      $badge      – (optional) text for a secondary badge
--}}
@props(['title', 'backRoute' => null, 'subtitle' => null, 'badge' => null])

<div class="d-flex align-items-center gap-2 mb-4">
    @if($backRoute)
    <a href="{{ $backRoute }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    @endif

    <div>
        <h4 class="fw-semibold mb-0">{{ $title }}</h4>
        @if($subtitle)
        <div class="text-muted small">{{ $subtitle }}</div>
        @endif
    </div>

    @if($badge)
    <span class="badge text-bg-secondary ms-auto">{{ $badge }}</span>
    @endif

    {{ $slot }}
</div>
