@extends('layouts.app')

@section('title', $post->title)

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}#daftar">Cafe</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $post->title }}</li>
        </ol>
    </nav>

    <div class="row g-4 align-items-start">
        <div class="col-lg-7">
            <img class="detail-cover" src="{{ $post->gambar_url }}" alt="Foto {{ $post->title }}">
        </div>
        <div class="col-lg-5">
            <span class="tag">{{ $post->category->title }}</span>
            <h1 class="display-6 mt-1 mb-3">{{ $post->title }}</h1>
            @include('partials.rating', ['avg' => $post->reviews_avg_rating, 'count' => $post->reviews_count])
            <p class="mt-3">{{ $post->body }}</p>
            <p class="text-muted mb-0"><i class="fa-solid fa-location-dot me-2"></i>{{ $post->alamat }}, {{ $post->kota }}, {{ $post->provinsi }}</p>
        </div>
    </div>

    <section class="mt-5">
        <h2 class="section-title">Lokasi</h2>
        <iframe class="map-frame" title="Peta lokasi {{ $post->title }}" loading="lazy"
                src="https://maps.google.com/maps?q={{ $post->latitude }},{{ $post->longitude }}&z=15&output=embed"></iframe>
    </section>

    <section id="produk" class="mt-5">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <h2 class="section-title mb-0">Menu &amp; Produk</h2>

            @if ($productsTotal > 0)
                <form action="{{ route('home.show', $post) }}" method="GET" class="filter-bar filter-bar-lg" role="search" data-live data-live-target="#product-results">
                    @include('partials.search-pill', ['search' => $search, 'placeholder' => 'Cari produk'])

                    <label for="sort" class="visually-hidden">Urutkan rating</label>
                    <select id="sort" name="sort" class="form-select">
                        <option value="">Urutkan: default</option>
                        <option value="rating_desc" @selected($sort === 'rating_desc')>Rating tertinggi</option>
                        <option value="rating_asc" @selected($sort === 'rating_asc')>Rating terendah</option>
                    </select>

                    <button type="button" class="btn btn-outline-primary" data-live-reset @if ($search === '' && $sort === '') hidden @endif>Reset</button>
                </form>
            @endif
        </div>
        <div id="product-results" aria-live="polite">
        <div class="row g-4">
            @forelse ($products as $product)
                <div class="col-md-6 col-lg-4">
                    <article class="card-cafe">
                        <img class="thumb" src="{{ $product->gambar_url }}" alt="Foto {{ $product->title }}" loading="lazy">
                        <div class="card-body">
                            <h3 class="card-title"><a href="{{ route('product.show', $product) }}">{{ $product->title }}</a></h3>
                            <p class="mb-0 text-muted">{{ \Illuminate\Support\Str::limit($product->body, 70) }}</p>
                            @include('partials.rating', ['avg' => $product->reviews_avg_rating, 'count' => $product->reviews_count])
                            <div class="card-actions pt-2">
                                <div class="price fs-5 mb-2">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                                @auth
                                    <form action="{{ route('cart.add', $product) }}" method="POST" class="d-flex gap-2">
                                        @csrf
                                        @include('partials.qty')
                                        <button class="btn btn-primary btn-sm flex-grow-1"><i class="fa-solid fa-plus me-1"></i> Keranjang</button>
                                    </form>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Masuk untuk memesan</a>
                                @endauth
                            </div>
                        </div>
                    </article>
                </div>
            @empty
                <div class="col-12"><div class="panel empty"><i class="fa-solid fa-mug-saucer"></i>{{ $productsTotal > 0 ? 'Produk yang kamu cari tidak ditemukan.' : 'Produk belum tersedia untuk saat ini.' }}</div></div>
            @endforelse
        </div>
        </div>
    </section>

    @include('partials.reviews', ['reviews' => $reviews, 'storeRoute' => route('reviews.post', $post)])
@endsection
