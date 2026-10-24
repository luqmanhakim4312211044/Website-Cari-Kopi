@extends('layouts.dashboard')
@section('title', 'Kelola Cafe')
@section('panel')
    <div class="page-head">
        <h1>{{ auth()->user()->isAdmin() ? 'Semua cafe' : 'Cafe saya' }}</h1>
        <a href="{{ route('posts.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Tambah cafe</a>
    </div>

    @include('partials.list-filter', ['route' => 'posts.index', 'search' => $search, 'sort' => $sort, 'placeholder' => 'Cari nama, kota, atau alamat'])

    <div id="results" aria-live="polite">
    <div class="panel overflow-hidden">
        <div class="table-responsive">
            <table class="table table-coffee">
                <thead><tr><th>Cafe</th><th>Kategori</th><th>Lokasi</th>@can('kelola_web')<th>Pemilik</th>@endcan<th class="text-center">Produk</th><th><span class="visually-hidden">Aksi</span></th></tr></thead>
                <tbody>
                    @forelse ($posts as $post)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <img class="thumb-sm" src="{{ $post->gambar_url }}" alt="">
                                    <a href="{{ route('home.show', $post) }}" class="fw-semibold text-decoration-none">{{ $post->title }}</a>
                                </div>
                            </td>
                            <td>{{ $post->category->title }}</td>
                            <td>{{ $post->kota }}, {{ $post->provinsi }}</td>
                            @can('kelola_web')<td>{{ $post->user->name }}</td>@endcan
                            <td class="text-center">{{ $post->products_count }}</td>
                            <td class="text-end text-nowrap">
                                @can('update', $post)<a href="{{ route('posts.edit', $post) }}" class="btn btn-outline-primary btn-sm">Edit</a>@endcan
                                @can('delete', $post)
                                    <form action="{{ route('posts.destroy', $post) }}" method="POST" class="d-inline" data-confirm="Hapus cafe ini beserta semua produknya?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm">Hapus</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty"><i class="fa-solid fa-store"></i>{{ $search !== '' ? 'Tidak ada cafe yang cocok.' : 'Belum ada cafe.' }}</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $posts->links() }}</div>
    </div>
@endsection
