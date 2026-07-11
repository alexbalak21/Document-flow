{{-- Sidebar partial – included by layouts/auth.blade.php --}}
<div id="sidebar">

    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <i class="bi bi-file-earmark-text brand-icon"></i>
        <span class="brand-text">Document Flow</span>
    </a>

    <nav class="sidebar-nav">

        <a href="{{ route('dashboard') }}"
           class="nav-item-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            <span class="nav-text">Dashboard</span>
        </a>

        @include('layouts._sidebar-doc-types')

        <a href="{{ route('documents.history') }}"
           class="nav-item-link {{ request()->routeIs('documents.history') && !request()->query('type') ? 'active' : '' }}">
            <i class="bi bi-archive"></i>
            <span class="nav-text">All Documents</span>
        </a>

        <div class="sidebar-label">Management</div>

        <a href="{{ route('customers.index') }}"
           class="nav-item-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i>
            <span class="nav-text">Customers</span>
        </a>

        <a href="{{ route('products.index') }}"
           class="nav-item-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i>
            <span class="nav-text">Products</span>
        </a>

        <a href="{{ route('templates.index') }}"
           class="nav-item-link {{ request()->routeIs('templates.*') ? 'active' : '' }}">
            <i class="bi bi-puzzle"></i>
            <span class="nav-text">Templates</span>
        </a>

        <a href="{{ route('settings.company') }}"
           class="nav-item-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
            <i class="bi bi-gear"></i>
            <span class="nav-text">Company Settings</span>
        </a>

    </nav>

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
