@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <h1 class="h2 mb-1">Selamat datang kembali</h1>
    <p class="text-muted mb-4">Masuk untuk memesan dan menulis ulasan.</p>

    <form action="{{ route('login.verify') }}" method="POST" novalidate>
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror"
                   autocomplete="email" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" autocomplete="current-password" required>
        </div>
        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Ingat saya</label>
        </div>
        <button class="btn btn-primary w-100 py-2">Masuk</button>
    </form>

    <p class="text-center text-muted mt-4 mb-0">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
@endsection
