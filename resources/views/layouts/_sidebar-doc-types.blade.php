{{-- Dynamic document-type nav groups --}}
@php
    $docTypes = \App\Models\DocumentType::where('active', true)
                    ->orderByRaw('sidebar_group_order IS NULL, sidebar_group_order')
                    ->orderBy('sidebar_group')
                    ->orderByRaw('sidebar_order IS NULL, sidebar_order')
                    ->orderBy('name')
                    ->get()
                    ->reject(fn($dt) => $dt->sidebar_hidden);
    $groups   = $docTypes->groupBy(fn($dt) => $dt->sidebar_group ?: 'Documents');
@endphp

@foreach($groups as $groupName => $groupTypes)
<div class="sidebar-label">{{ $groupName }}</div>

@foreach($groupTypes as $dt)
@php
    // A group should stay expanded (and its History link marked active)
    // whether we're on Overview, New, or History for this document type.
    // Overview/New URLs start with /documents/{slug}; History is a
    // separate route (/history?type={slug}) and needs its own check.
    $onHistoryForType = request()->routeIs('documents.history')
        && request()->query('type') === $dt->slug;
    $groupIsActive = request()->is('documents/'.$dt->slug.'*') || $onHistoryForType;
@endphp
<div class="nav-group">
    <div class="nav-group-toggle {{ $groupIsActive ? 'open' : '' }}"
         onclick="toggleGroup(this)">
        <i class="bi {{ $dt->icon_display }} icon"></i>
        <span class="nav-text">{{ $dt->sidebar_label_display }}</span>
        <i class="bi bi-chevron-right chevron"></i>
    </div>
    <div class="nav-group-children collapse {{ $groupIsActive ? 'show' : '' }}">
        <a href="{{ route('documents.page', $dt->slug) }}"
           class="nav-item-link {{ request()->routeIs('documents.page') && request()->route('slug') === $dt->slug ? 'active' : '' }}">
            <i class="bi bi-grid-3x3-gap"></i>
            <span class="nav-text">Overview</span>
        </a>
        <a href="{{ route('documents.create', $dt->slug) }}"
           class="nav-item-link {{ request()->routeIs('documents.create') && request()->route('slug') === $dt->slug ? 'active' : '' }}">
            <i class="bi bi-plus-circle"></i>
            <span class="nav-text">New {{ $dt->sidebar_label_display }}</span>
        </a>
        <a href="{{ route('documents.history', ['type' => $dt->slug]) }}"
           class="nav-item-link {{ $onHistoryForType ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i>
            <span class="nav-text">History</span>
        </a>
    </div>
</div>
@endforeach
@endforeach