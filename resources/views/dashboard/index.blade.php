@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('panel')
    <div class="page-head">
        <div>
            <h1>Halo, {{ $user->name }}</h1>
            <span class="badge-soft badge-member">{{ $user->role_label }}</span>
            <span class="text-muted small ms-1">{{ $user->isAdmin() ? 'Menampilkan data seluruh situs' : 'Menampilkan data tokomu' }}</span>
        </div>
        <a href="{{ route('posts.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> Tambah cafe</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="stat"><div class="num">{{ $stats['cafes'] }}</div><div class="label">Cafe</div></div></div>
        <div class="col-6 col-xl-3"><div class="stat"><div class="num">{{ $stats['products'] }}</div><div class="label">Produk</div></div></div>
        <div class="col-6 col-xl-3"><div class="stat"><div class="num">{{ $stats['pending'] }}</div><div class="label">Pesanan belum bayar</div></div></div>
        @if ($stats['users'] !== null)
            <div class="col-6 col-xl-3"><div class="stat"><div class="num">{{ $stats['users'] }}</div><div class="label">Pengguna</div></div></div>
        @endif
        <div class="col-6 col-xl-3"><div class="stat"><div class="num fs-4">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</div><div class="label">Pendapatan (sudah bayar)</div></div></div>
    </div>

    <div class="panel overflow-hidden">
        <div class="panel-body d-flex justify-content-between align-items-center pb-3">
            <h2 class="h5 mb-0">Pesanan terbaru</h2>
            <a href="{{ route('dashboard.orders') }}" class="small">Lihat semua</a>
        </div>
        <div class="table-responsive">
            <table class="table table-coffee">
                <thead><tr><th>#</th><th>Pemesan</th><th>Tanggal</th><th class="text-end">Total</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($recentOrders as $order)
                        <tr>
                            <td><a href="{{ route('orders.show', [$order, 'from' => 'dashboard']) }}">{{ $order->id }}</a></td>
                            <td>{{ $order->nama }}</td>
                            <td class="text-nowrap">{{ $order->created_at->translatedFormat('d M Y, H:i') }} WIB</td>
                            <td class="text-end text-nowrap">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td>@include('partials.status-badge', ['order' => $order])</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty"><i class="fa-solid fa-inbox"></i>Belum ada pesanan masuk.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
