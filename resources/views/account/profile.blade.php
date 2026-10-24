@extends('layouts.app')

@section('title', 'Profil')

@section('content')
    <div class="page-head"><h1>Profil</h1></div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="panel panel-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="avatar" style="width:64px;height:64px;font-size:1.5rem" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</div>
                    <div>
                        <h2 class="h4 mb-0">{{ $user->name }}</h2>
                        <span class="text-muted">{{ $user->email }}</span>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-6 col-sm-4"><div class="stat"><div class="num">{{ $ordersCount }}</div><div class="label">Pesanan</div></div></div>
                    <div class="col-6 col-sm-4"><div class="stat"><div class="num fs-5">{{ $user->role_label }}</div><div class="label">Peran</div></div></div>
                    <div class="col-6 col-sm-4">
                        <div class="stat">
                            <div class="num text-capitalize">{{ $user->member }}</div>
                            <div class="label">Member{{ $user->discountRate() ? ' · diskon '.($user->discountRate() * 100).'%' : '' }}</div>
                        </div>
                    </div>
                </div>
                <p class="text-muted small mt-3 mb-0">Bergabung sejak {{ $user->created_at->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="panel panel-body">
                <h2 class="h5 mb-3">Ubah password</h2>
                <form action="{{ route('password.update') }}" method="POST">
                    @csrf @method('PATCH')
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password saat ini</label>
                        <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password baru</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" minlength="8" required>
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Ulangi password baru</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                    </div>
                    <button class="btn btn-primary">Simpan password</button>
                </form>
            </div>
        </div>
    </div>

@endsection
