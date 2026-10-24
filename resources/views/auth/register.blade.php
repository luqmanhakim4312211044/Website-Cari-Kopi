@extends('layouts.guest')

@section('title', 'Daftar')

@section('content')
    <h1 class="h2 mb-1">Buat akun</h1>
    <p class="text-muted mb-4">Gratis, dan hanya butuh satu menit.</p>

    <form action="{{ route('register.store') }}" method="POST" novalidate>
        @csrf
        <div class="mb-3">
            <label for="name" class="form-label">Nama</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror"
                   autocomplete="name" required autofocus>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror"
                   autocomplete="email" placeholder="nama@gmail.com" required>
            <div class="form-text">Gunakan email aktif, misalnya Gmail atau Yahoo.</div>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror"
                   autocomplete="new-password" minlength="8" required>
            <div class="form-text">Minimal 8 karakter.</div>
        </div>
        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Ulangi password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                   autocomplete="new-password" required>
        </div>
        <button class="btn btn-primary w-100 py-2">Daftar</button>
    </form>

    <p class="text-center text-muted mt-4 mb-0">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
@endsection
