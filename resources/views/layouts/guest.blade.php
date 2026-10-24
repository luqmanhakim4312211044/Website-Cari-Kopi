<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <title>@yield('title') CariKopi </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet" integrity="sha384-PPIZEGYM1v8zp5Py7UjFb79S58UeqCL9pYVnVPURKEqvioPROaVAJKKLzvH2rDnI" crossorigin="anonymous">
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">
</head>
<body>
<div class="auth-shell">
    <aside class="auth-art">
        <div>
            <a href="{{ route('home') }}" class="brand-font text-white text-decoration-none fs-5 d-inline-flex align-items-center gap-2 mb-3">
                <i class="fa-solid fa-mug-hot" style="color: var(--gold)"></i> CariKopi
            </a>
            <h2>Temukan kafe favoritmu.</h2>
        </div>
    </aside>

    <section class="auth-form">
        <div class="auth-card">
            <div><a href="{{ route('home') }}" class="back-link"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Kembali ke beranda</a></div>
            <div class="d-md-none"><a href="{{ route('home') }}" class="brand-font text-decoration-none fs-5 d-inline-flex align-items-center gap-2 mb-4" style="color: var(--espresso)">
                <i class="fa-solid fa-mug-hot" style="color: var(--accent)"></i> CariKopi
            </a></div>
                @yield('content')
        </div>
    </section>
</div>
@include('partials.notify')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.8/dist/sweetalert2.all.min.js" integrity="sha384-26j4cFQoN0CRqI+nXAEeV3E2O9VkYqOJJGH+DQSQIBD0LfvCjUBDqXxi5feRZ0dn" crossorigin="anonymous"></script>
<script src="{{ asset('js/app.js') }}?v={{ filemtime(public_path('js/app.js')) }}"></script>
</body>
</html>