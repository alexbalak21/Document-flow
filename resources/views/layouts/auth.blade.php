<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
    @include('layouts._sidebar-styles')
</head>
<body>

@include('layouts._sidebar')

<button id="sidebar-toggle" onclick="toggleSidebar()" title="Toggle sidebar">
    <i class="bi bi-chevron-left" id="toggle-icon"></i>
</button>

<div id="main">
    <div class="topbar">
        <div class="dropdown">
            <button class="btn btn-sm btn-link text-muted small text-decoration-none dropdown-toggle p-0"
                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name ?? 'Admin' }}
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class="bi bi-key me-2"></i>Change Password
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <div class="p-4">
        <x-ui.alert-flash />
        @yield('content')
    </div>
</div>

<x-ui.change-password-modal />

@include('layouts._sidebar-js')

@if ($errors->hasAny(['current_password', 'password']))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('changePasswordModal')).show();
    });
</script>
@endif

</body>
</html>