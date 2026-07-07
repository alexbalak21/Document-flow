@extends('layouts.auth')

@section('title', 'Dashboard')

@section('content')
<div class="container py-4">
    <h4 class="fw-semibold">Dashboard</h4>
    <p class="text-muted">Welcome back.</p>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-outline-secondary btn-sm">Logout</button>
    </form>
</div>
@endsection