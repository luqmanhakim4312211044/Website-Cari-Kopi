@extends('layouts.app')

{{-- Kerangka dashboard: sidebar di kiri (desktop) / tombol hamburger + offcanvas kiri (layar kecil). Isi halaman di @section('panel'). --}}
@section('nav-toggle')
    <button class="btn btn-light-soft btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#dashMenu"
            aria-controls="dashMenu" aria-label="Buka menu dashboard">
        <i class="fa-solid fa-bars"></i>
    </button>
@endsection

@section('content')
    <div class="offcanvas offcanvas-start" tabindex="-1" id="dashMenu" aria-labelledby="dashMenuLabel">
        <div class="offcanvas-header">
            <h2 class="offcanvas-title h5 brand-font" id="dashMenuLabel">Menu dashboard</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
        </div>
        <div class="offcanvas-body pt-0">
            @include('partials.dash-nav')
        </div>
    </div>

    <div class="row g-4">
        <aside class="col-lg-3 col-xl-2 d-none d-lg-block">
            @include('partials.dash-nav')
        </aside>

        <div class="col-lg-9 col-xl-10">
            @yield('panel')
        </div>
    </div>
@endsection
