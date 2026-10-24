@extends('layouts.app')

@section('title', $product->title.' - '.$post->title)

@section('content')
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}#daftar">Cafe</a></li>
            <li class="breadcrumb-item"><a href="{{ route('home.show', $post) }}">{{ $post->title }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $product->title }}</li>
        </ol>
    </nav>

    <div class="row g-4 align-items-start">
        <div class="col-lg-6">
            <img class="detail-cover" src="{{ $product->gambar_url }}" alt="Foto {{ $product->title }}">
        </div>
        <div class="col-lg-6">
            <a href="{{ route('home.show', $post) }}" class="tag text-decoration-none">{{ $post->title }}</a>
            <h1 class="display-6 mt-1 mb-3">{{ $product->title }}</h1>
            @include('partials.rating', ['avg' => $product->reviews_avg_rating, 'count' => $product->reviews_count])
            <p class="price fs-3 my-3">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
            <p>{{ $product->body }}</p>

            @auth
                <form action="{{ route('cart.add', $product) }}" method="POST" class="d-flex flex-wrap align-items-center gap-3">
                    @csrf
                    @include('partials.qty', ['large' => true])
                    <button class="btn btn-primary btn-lg"><i class="fa-solid fa-bag-shopping me-2"></i>Tambah ke keranjang</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg">Masuk untuk memesan</a>
            @endauth
        </div>
    </div>

    @include('partials.reviews', ['reviews' => $reviews, 'storeRoute' => route('reviews.product', $product)])
@endsection
