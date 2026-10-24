@extends('layouts.app')

@section('title', 'CariKopi')

@section('hero')
    <header class="hero py-5">
        <div class="container py-4 py-md-5">
            <p class="eyebrow mb-2">Temukan &amp; pesan</p>
            <h1 class="mb-3">Cari kafe terbaik,<br>di sekitarmu.</h1>
            <p class="lead mb-4">Daftar dan jelajahi berbagai kafe lengkap dengan menu, lokasi, dan ulasan dari pengunjung lain.</p>

            <a href="#daftar" class="btn btn-primary btn-lg px-4">
                Jelajahi cafe <i class="fa-solid fa-arrow-down ms-2"></i>
            </a>
        </div>
    </header>
@endsection

@section('content')
    <div id="daftar" class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <h2 class="mb-0 h3" id="list-title">
            @if ($search !== '') Hasil untuk &ldquo;{{ $search }}&rdquo; @else Daftar Cafe @endif
        </h2>

        <form action="{{ route('home') }}" method="GET" class="filter-bar filter-bar-lg" role="search" data-live data-live-target="#results, #list-title">
            @include('partials.search-pill', ['search' => $search, 'placeholder' => 'Cari nama, deskripsi, atau lokasi'])

            <label for="category" class="visually-hidden">Kategori</label>
            <select id="category" name="category" class="form-select">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($categoryId == $category->id)>{{ $category->title }}</option>
                @endforeach
            </select>

            <label for="sort" class="visually-hidden">Urutkan rating</label>
            <select id="sort" name="sort" class="form-select">
                <option value="">Urutkan: terbaru</option>
                <option value="rating_desc" @selected($sort === 'rating_desc')>Rating tertinggi</option>
                <option value="rating_asc" @selected($sort === 'rating_asc')>Rating terendah</option>
            </select>

            <button type="button" class="btn btn-outline-primary" data-live-reset @if ($search === '' && ! $categoryId && $sort === '') hidden @endif>Reset</button>
        </form>
    </div>

    <div id="results" aria-live="polite">
    <div class="row g-4">
        @forelse ($posts as $post)
            <div class="col-md-6 col-lg-4">
                <article class="card-cafe">
                    <img class="thumb" src="{{ $post->gambar_url }}" alt="Foto {{ $post->title }}" loading="lazy">
                    <div class="card-body">
                        <span class="tag">{{ $post->category->title }}</span>
                        <h3 class="card-title"><a href="{{ route('home.show', $post) }}">{{ $post->title }}</a></h3>
                        <p class="text-muted small mb-0"><i class="fa-solid fa-location-dot me-1"></i>{{ $post->kota }}, {{ $post->provinsi }}</p>
                        <p class="mb-1">{{ \Illuminate\Support\Str::limit($post->body, 100) }}</p>
                        <div class="card-actions">
                            @include('partials.rating', ['avg' => $post->reviews_avg_rating, 'count' => $post->reviews_count])
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="panel empty">
                    <i class="fa-solid fa-mug-saucer"></i>
                    Maaf, cafe yang kamu cari tidak ditemukan.
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $posts->fragment('daftar')->links() }}</div>
    </div>
@endsection
