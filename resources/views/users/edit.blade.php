@extends('layouts.dashboard')
@section('title', 'Ubah Pengguna')
@section('panel')
    <div class="page-head"><h1>Ubah pengguna: {{ $user->name }}</h1></div>

    <form action="{{ route('users.update', $user) }}" method="POST" class="panel panel-body" style="max-width:34rem">
        @csrf @method('PATCH')

        <div class="mb-3">
            <label for="role" class="form-label">Peran</label>
            <select id="role" name="role" class="form-select" @disabled($user->is(auth()->user()))>
                @foreach (\App\Models\User::ROLES as $value => $label)
                    <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @if ($user->is(auth()->user()))
                <input type="hidden" name="role" value="{{ $user->role }}">
                <div class="form-text">Kamu tidak bisa mengubah peran akunmu sendiri.</div>
            @else
                <div class="form-text">
                    <strong>Admin Web</strong> mengontrol seluruh data. <strong>Admin Toko</strong> hanya mengelola cafe, produk, dan pesanan miliknya. <strong>Pelanggan</strong> hanya bisa berbelanja.
                </div>
            @endif
        </div>

        <div class="mb-4">
            <label for="member" class="form-label">Level member</label>
            <select id="member" name="member" class="form-select">
                @foreach (\App\Models\User::MEMBER_DISCOUNTS as $level => $rate)
                    <option value="{{ $level }}" @selected(old('member', $user->member) === $level)>
                        {{ ucfirst($level) }}{{ $rate ? ' — diskon '.($rate * 100).'%' : ' — tanpa diskon' }}
                    </option>
                @endforeach
            </select>
        </div>

        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('users.index') }}" class="btn btn-link">Batal</a>
    </form>
@endsection
