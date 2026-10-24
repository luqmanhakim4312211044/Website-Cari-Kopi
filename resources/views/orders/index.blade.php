@extends('layouts.app')

@section('title', 'Pesanan Saya')

@section('content')
    <div class="page-head">
        <h1>Pesanan saya</h1>
        <nav class="d-flex flex-wrap gap-2" aria-label="Filter status">
            <a href="{{ route('orders.index') }}" class="chip {{ ! $status ? 'active' : '' }}">Semua</a>
            @foreach (\App\Models\Order::STATUSES as $key => $label)
                <a href="{{ route('orders.index', ['status' => $key]) }}" class="chip {{ $status === $key ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </nav>
    </div>

    <div class="panel overflow-hidden">
        <div class="table-responsive">
            <table class="table table-coffee">
                <thead>
                    <tr><th>#</th><th>Tanggal</th><th>Penjual</th><th class="text-end">Total</th><th>Status</th><th><span class="visually-hidden">Aksi</span></th></tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->id }}</td>
                            <td class="text-nowrap">{{ $order->created_at->translatedFormat('d M Y, H:i') }} WIB</td>
                            <td>{{ $order->seller?->name ?? '—' }}</td>
                            <td class="text-end text-nowrap">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td>@include('partials.status-badge', ['order' => $order])</td>
                            <td class="text-end"><a href="{{ route('orders.show', $order) }}" class="btn btn-outline-primary btn-sm">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty"><i class="fa-solid fa-receipt"></i>Belum ada pesanan.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
