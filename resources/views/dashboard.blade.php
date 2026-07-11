@extends('layouts.auth')

@section('title', 'Dashboard')

@section('content')

<h4 class="fw-semibold mb-4">Dashboard</h4>

{{-- Summary stats row --}}
@include('dashboard._stats')

{{-- Per document-type breakdown --}}
@include('dashboard._type-stats')

{{-- Recent documents table --}}
@include('dashboard._recent-docs')

@endsection
