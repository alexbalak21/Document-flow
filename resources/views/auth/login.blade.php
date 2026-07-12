@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div style="width:100%; max-width:400px; padding: 0 16px;">

    <div class="text-center mb-4">
        <div class="mb-3">
            <i class="bi bi-file-earmark-text" style="font-size:40px; color:#1a56db;"></i>
        </div>
        <h4 class="fw-semibold">{{ config('app.name') }}</h4>
        <p class="text-muted small">Sign in to continue</p>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            @if ($errors->any())
            <x-ui.alert type="danger" :dismissible="false" :small="true">
                <i class="bi bi-exclamation-circle me-1"></i>
                {{ $errors->first() }}
            </x-ui.alert>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label fw-medium">Email</label>
                    <input type="email" id="email" name="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}" required autofocus>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-medium">Password</label>
                    <input type="password" id="password" name="password"
                        class="form-control @error('password') is-invalid @enderror"
                        required>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign in
                </button>
            </form>

        </div>
    </div>
    <!-- FOR DEVELOPMENT PURPOSES ONLY - TO DELETE BEFORE PRODUCTION -->
    <script>
        document.getElementById('email').value = "admin@mail.com";
        document.getElementById('password').value = "password123";
    </script>
    <!-- FOR DEVELOPMENT PURPOSES ONLY - TO DELETE BEFORE PRODUCTION -->

    <p class="text-center text-muted small mt-3">{{ config('app.name') }}</p>
</div>
@endsection