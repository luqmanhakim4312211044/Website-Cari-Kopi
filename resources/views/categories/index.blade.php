@extends('layouts.dashboard')
@section('title', 'Kelola Kategori')
@section('panel')
    <div class="page-head">
        <h1>Kategori</h1>
        <a href="{{ route('categories.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Tambah kategori</a>
    </div>

    <div class="panel overflow-hidden">
        <div class="table-responsive">
            <table class="table table-coffee">
                <thead><tr><th>Nama</th><th class="text-center">Jumlah cafe</th><th><span class="visually-hidden">Aksi</span></th></tr></thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td class="fw-semibold">{{ $category->title }}</td>
                            <td class="text-center">{{ $category->posts_count }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('categories.edit', $category) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline" data-confirm="Hapus kategori ini?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" @disabled($category->posts_count > 0) title="{{ $category->posts_count > 0 ? 'Masih dipakai cafe' : '' }}">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty"><i class="fa-solid fa-tags"></i>Belum ada kategori.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
