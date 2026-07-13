<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
        <span class="text-muted small">{{ Auth::user()->name ?? 'Admin' }}</span>
    </div>

    <div class="p-4">
        <x-ui.alert-flash />
        @yield('content')
    </div>
</div>

@include('layouts._sidebar-js')

</body>
</html>
