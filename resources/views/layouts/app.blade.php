<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <title>@yield('title', 'CariKopi')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" integrity="sha384-PPIZEGYM1v8zp5Py7UjFb79S58UeqCL9pYVnVPURKEqvioPROaVAJKKLzvH2rDnI" crossorigin="anonymous">
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">

<nav class="navbar site-nav py-2">
    <div class="container flex-nowrap">
        <div class="d-flex align-items-center gap-2">
            @yield('nav-toggle')
            <a class="navbar-brand brand-font m-0" href="{{ route('home') }}">
                <i class="fa-solid fa-mug-hot"></i> CariKopi
            </a>
        </div>

        <div class="d-flex align-items-center gap-2 ms-auto">
            @auth
                <a href="{{ route('cart') }}" class="cart-link" aria-label="Keranjang, {{ $navCart->sum('quantity') }} item">
                    <i class="fa-solid fa-bag-shopping"></i>
                    @if ($navCart->isNotEmpty())
                        <span class="cart-badge">{{ $navCart->sum('quantity') }}</span>
                    @endif
                </a>

                <div class="dropdown">
                    <button class="btn btn-light-soft btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Menu akun">
                        <i class="fa-regular fa-user"></i>
                        <span class="d-none d-sm-inline ms-1">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 16) }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile') }}"><i class="fa-regular fa-id-card fa-fw me-2"></i>Profil</a></li>
                        <li><a class="dropdown-item" href="{{ route('orders.index') }}"><i class="fa-solid fa-receipt fa-fw me-2"></i>Pesanan saya</a></li>
                        @can('kelola_toko')
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('dashboard') }}"><i class="fa-solid fa-gauge fa-fw me-2"></i>Dashboard</a></li>
                        @endcan
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button class="dropdown-item"><i class="fa-solid fa-arrow-right-from-bracket fa-fw me-2"></i>Keluar</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <a href="{{ route('login') }}" class="btn btn-light-soft btn-sm">Masuk</a>
                <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Daftar</a>
            @endauth
        </div>
    </div>
</nav>

<main class="flex-grow-1">
    @yield('hero')

    <div class="container py-4 py-md-5">
        @yield('content')
    </div>
</main>

<footer class="site-footer py-4 mt-auto">
    <div class="container d-flex flex-wrap justify-content-between gap-2">
        <span><i class="fa-solid fa-mug-hot me-1"></i> CariKopi - tempatnya cari kopi.</span>
        <span>&copy; {{ date('Y') }}</span>
    </div>
</footer>

@include('partials.notify')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.all.min.js" integrity="sha384-26j4cFQoN0CRqI+nXAEeV3E2O9VkYqOJJGH+DQSQIBD0LfvCjUBDqXxi5feRZ0dn" crossorigin="anonymous"></script>
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
@stack('scripts')
</body>
</html>
