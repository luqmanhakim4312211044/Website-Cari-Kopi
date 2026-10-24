@extends('layouts.dashboard')
@section('title', 'Kelola Pengguna')
@section('panel')
    <div class="page-head"><h1>Pengguna</h1></div>

    @include('partials.list-filter', ['route' => 'users.index', 'search' => $search, 'sort' => $sort, 'placeholder' => 'Cari nama atau email'])

    <div id="results" aria-live="polite">
    <div class="panel overflow-hidden">
        <div class="table-responsive">
            <table class="table table-coffee">
                <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Member</th><th class="text-center">Pesanan</th><th><span class="visually-hidden">Aksi</span></th></tr></thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td class="fw-semibold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role_label }}</td>
                            <td><span class="badge-soft badge-member">{{ $user->member }}</span></td>
                            <td class="text-center">{{ $user->orders_count }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-primary btn-sm">Edit</a>
                                @if (! $user->is(auth()->user()))
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" data-confirm="Hapus pengguna ini? Cafe dan produk miliknya juga ikut terhapus.">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty"><i class="fa-solid fa-users"></i>Tidak ada pengguna yang cocok.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
    </div>
@endsection
