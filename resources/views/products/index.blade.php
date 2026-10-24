@extends('layouts.dashboard')
@section('title', 'Kelola Produk')
@section('panel')
    <div class="page-head">
        <h1>{{ auth()->user()->isAdmin() ? 'Semua produk' : 'Produk saya' }}</h1>
        <a href="{{ route('products.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Tambah produk</a>
    </div>

    @include('partials.list-filter', ['route' => 'products.index', 'search' => $search, 'sort' => $sort, 'placeholder' => 'Cari nama atau deskripsi produk'])

    <div id="results" aria-live="polite">
    <div class="panel overflow-hidden">
        <div class="table-responsive">
            <table class="table table-coffee">
                <thead><tr><th>Produk</th><th>Cafe</th>@can('kelola_web')<th>Pemilik</th>@endcan<th class="text-end">Harga</th><th><span class="visually-hidden">Aksi</span></th></tr></thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img class="thumb-sm" src="{{ $product->gambar_url }}" alt="">
                                    <div>
                                        <a href="{{ route('product.show', $product) }}" class="fw-semibold text-decoration-none">{{ $product->title }}</a>
                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($product->body, 60) }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->post->title }}</td>
                            @can('kelola_web')<td>{{ $product->post->user->name }}</td>@endcan
                            <td class="text-end text-nowrap">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                            <td class="text-end text-nowrap">
                                @can('update', $product)<a href="{{ route('products.edit', $product) }}" class="btn btn-outline-primary btn-sm">Edit</a>@endcan
                                @can('delete', $product)
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" data-confirm="Hapus produk ini?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm">Hapus</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty"><i class="fa-solid fa-mug-saucer"></i>{{ $search !== '' ? 'Tidak ada produk yang cocok.' : 'Belum ada produk.' }}</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
    </div>
@endsection
