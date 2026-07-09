<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    <style>
        :root { --sidebar-width: 240px; }

        body { background: #f4f6f9; }

        /* Sidebar */
        #sidebar {
            position: fixed;
            top: 0; left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: #1e2533;
            display: flex;
            flex-direction: column;
            transition: width .2s ease;
            z-index: 1000;
            overflow: hidden;
        }

        #sidebar.collapsed { width: 56px; }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 16px;
            color: #fff;
            font-weight: 700;
            font-size: 15px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            white-space: nowrap;
            text-decoration: none;
        }

        .sidebar-brand .brand-icon {
            font-size: 20px;
            flex-shrink: 0;
            color: #60a5fa;
        }

        .sidebar-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,.35);
            padding: 14px 16px 4px;
            white-space: nowrap;
        }

        .sidebar-nav { flex: 1; overflow-y: auto; overflow-x: hidden; padding-bottom: 12px; }

        .nav-item-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 16px;
            color: rgba(255,255,255,.7);
            text-decoration: none;
            font-size: 13.5px;
            border-left: 3px solid transparent;
            white-space: nowrap;
            transition: background .15s, color .15s;
        }

        .nav-item-link:hover,
        .nav-item-link.active {
            background: rgba(255,255,255,.07);
            color: #fff;
            border-left-color: #60a5fa;
        }

        .nav-item-link i { font-size: 15px; flex-shrink: 0; width: 20px; text-align: center; }

        /* Collapsible doc-type group */
        .nav-group-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 16px;
            color: rgba(255,255,255,.7);
            font-size: 13.5px;
            cursor: pointer;
            border-left: 3px solid transparent;
            white-space: nowrap;
            user-select: none;
            transition: background .15s;
        }

        .nav-group-toggle:hover { background: rgba(255,255,255,.07); color: #fff; }
        .nav-group-toggle i.icon { font-size: 15px; flex-shrink: 0; width: 20px; text-align: center; }
        .nav-group-toggle .chevron { margin-left: auto; font-size: 11px; transition: transform .2s; }
        .nav-group-toggle.open .chevron { transform: rotate(90deg); }

        .nav-group-children { padding-left: 16px; }

        .nav-group-children .nav-item-link {
            font-size: 13px;
            padding: 7px 16px;
            color: rgba(255,255,255,.55);
        }

        /* Sidebar footer */
        .sidebar-footer {
            border-top: 1px solid rgba(255,255,255,.08);
            padding: 10px 8px;
        }

        .sidebar-footer .nav-item-link { font-size: 13px; border-radius: 6px; border-left: none; }

        /* Collapse toggle button */
        #sidebar-toggle {
            position: fixed;
            top: 14px;
            left: calc(var(--sidebar-width) - 14px);
            z-index: 1100;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: #fff;
            border: 1px solid #dee2e6;
            box-shadow: 0 1px 4px rgba(0,0,0,.12);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: left .2s ease;
            color: #6c757d;
            font-size: 11px;
        }

        #sidebar.collapsed ~ #sidebar-toggle { left: 42px; }

        /* Main content */
        #main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left .2s ease;
        }

        #sidebar.collapsed ~ #main { margin-left: 56px; }

        /* Hide text when collapsed */
        #sidebar.collapsed .sidebar-label,
        #sidebar.collapsed .nav-text,
        #sidebar.collapsed .chevron,
        #sidebar.collapsed .brand-text { display: none; }

        /* Top navbar */
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }
    </style>
</head>
<body>

{{-- ── SIDEBAR ──────────────────────────────────────────────────────────── --}}
<div id="sidebar">

    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <i class="bi bi-file-earmark-text brand-icon"></i>
        <span class="brand-text">Document Flow</span>
    </a>

    <nav class="sidebar-nav">

        {{-- Dashboard --}}
        <a href="{{ route('dashboard') }}"
           class="nav-item-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span class="nav-text">Dashboard</span>
        </a>

        {{-- Documents section --}}
        <div class="sidebar-label">Documents</div>

        @php
            $docTypes = \App\Models\DocumentType::where('active', true)->orderBy('name')->get();
        @endphp

        @foreach($docTypes as $dt)
        <div class="nav-group">
            <div class="nav-group-toggle {{ request()->is('documents/'.$dt->slug.'*') ? 'open' : '' }}"
                 onclick="toggleGroup(this)">
                <i class="bi bi-file-earmark icon"></i>
                <span class="nav-text">{{ $dt->name }}</span>
                <i class="bi bi-chevron-right chevron"></i>
            </div>
            <div class="nav-group-children collapse {{ request()->is('documents/'.$dt->slug.'*') ? 'show' : '' }}">
                <a href="{{ route('documents.page', $dt->slug) }}"
                   class="nav-item-link {{ request()->routeIs('documents.page') && request()->route('slug') === $dt->slug ? 'active' : '' }}">
                    <i class="bi bi-grid-3x3-gap"></i>
                    <span class="nav-text">{{ $dt->name }} Page</span>
                </a>
                <a href="{{ route('documents.create', $dt->slug) }}"
                   class="nav-item-link {{ request()->routeIs('documents.create') && request()->route('slug') === $dt->slug ? 'active' : '' }}">
                    <i class="bi bi-plus-circle"></i>
                    <span class="nav-text">New {{ $dt->name }}</span>
                </a>
                <a href="{{ route('documents.history', ['type' => $dt->slug]) }}"
                   class="nav-item-link">
                    <i class="bi bi-clock-history"></i>
                    <span class="nav-text">History</span>
                </a>
            </div>
        </div>
        @endforeach

        {{-- All history --}}
        <a href="{{ route('documents.history') }}"
           class="nav-item-link {{ request()->routeIs('documents.history') && !request()->query('type') ? 'active' : '' }}">
            <i class="bi bi-archive"></i>
            <span class="nav-text">All Documents</span>
        </a>

        {{-- Divider --}}
        <div class="sidebar-label">Management</div>

        <a href="{{ route('customers.index') }}"
           class="nav-item-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i>
            <span class="nav-text">Customers</span>
        </a>

        <a href="{{ route('templates.index') }}"
           class="nav-item-link {{ request()->routeIs('templates.*') ? 'active' : '' }}">
            <i class="bi bi-puzzle"></i>
            <span class="nav-text">Templates</span>
        </a>

    </nav>

    {{-- Footer --}}
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-item-link w-100 border-0 bg-transparent text-start">
                <i class="bi bi-box-arrow-right"></i>
                <span class="nav-text">Logout</span>
            </button>
        </form>
    </div>

</div>

{{-- Collapse toggle --}}
<button id="sidebar-toggle" onclick="toggleSidebar()" title="Toggle sidebar">
    <i class="bi bi-chevron-left" id="toggle-icon"></i>
</button>

{{-- ── MAIN ─────────────────────────────────────────────────────────────── --}}
<div id="main">

    <div class="topbar">
        <span class="text-muted small">{{ Auth::user()->name ?? 'Admin' }}</span>
    </div>

    <div class="p-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show py-2" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

</div>

<script>
// ── Sidebar collapse ────────────────────────────────────────────────────────
const sidebar     = document.getElementById('sidebar');
const toggleIcon  = document.getElementById('toggle-icon');
const STORAGE_KEY = 'df_sidebar_collapsed';

function toggleSidebar() {
    const collapsed = sidebar.classList.toggle('collapsed');
    toggleIcon.className = collapsed ? 'bi bi-chevron-right' : 'bi bi-chevron-left';
    localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
}

// Restore state on load
if (localStorage.getItem(STORAGE_KEY) === '1') {
    sidebar.classList.add('collapsed');
    toggleIcon.className = 'bi bi-chevron-right';
}

// ── Nav group collapse ──────────────────────────────────────────────────────
function toggleGroup(el) {
    el.classList.toggle('open');
    const children = el.nextElementSibling;
    if (children) {
        if (children.classList.contains('show')) {
            children.classList.remove('show');
        } else {
            children.classList.add('show');
        }
    }
}
</script>

</body>
</html>
