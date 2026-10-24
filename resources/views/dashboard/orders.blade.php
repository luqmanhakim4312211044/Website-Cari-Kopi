@extends('layouts.dashboard')

@section('title', 'Pesanan Masuk')

@section('panel')
    <div class="page-head">
        <h1>{{ auth()->user()->isAdmin() ? 'Semua pesanan' : 'Pesanan masuk' }}</h1>
        <nav class="d-flex flex-wrap gap-2" aria-label="Filter status" id="status-chips">
            @php
                $keep = array_filter(['search' => $search, 'sort' => $sort === 'oldest' ? 'oldest' : null]);
            @endphp
            <a href="{{ route('dashboard.orders', $keep) }}" class="chip {{ ! $status ? 'active' : '' }}">Semua</a>
            @foreach (\App\Models\Order::STATUSES as $key => $label)
                <a href="{{ route('dashboard.orders', $keep + ['status' => $key]) }}" class="chip {{ $status === $key ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </nav>
    </div>

    @include('partials.list-filter', ['route' => 'dashboard.orders', 'search' => $search, 'sort' => $sort, 'hidden' => ['status' => $status], 'targets' => '#results, #status-chips', 'placeholder' => 'Cari nama pemesan, nomor, produk, atau #pesanan'])

    <div id="results" aria-live="polite">
    <div class="panel overflow-hidden">
        <div class="table-responsive">
            <table class="table table-coffee">
                <thead>
                    <tr><th>#</th><th>Pemesan</th>@can('kelola_web')<th>Penjual</th>@endcan<th>Item</th><th class="text-end">Total</th><th style="min-width:11rem">Status</th><th><span class="visually-hidden">Aksi</span></th></tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td>{{ $order->id }}<div class="small text-muted text-nowrap">{{ $order->created_at->translatedFormat('d M, H:i') }} WIB</div></td>
                            <td>{{ $order->nama }}<div class="small text-muted text-nowrap" title="Nomor telepon pemesan"><i class="fa-solid fa-phone fa-xs me-1"></i>{{ $order->nomor }}</div></td>
                            @can('kelola_web')<td>{{ $order->seller?->name ?? '—' }}</td>@endcan
                            <td class="small">
                                @foreach ($order->items as $item)
                                    <div>{{ $item->nama }} <span class="text-muted">&times; {{ $item->jumlah }}</span></div>
                                @endforeach
                            </td>
                            <td class="text-end text-nowrap">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                            <td>
                                <form action="{{ route('dashboard.orders.update', $order) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" aria-label="Status pesanan #{{ $order->id }}">
                                        @foreach (\App\Models\Order::STATUSES as $key => $label)
                                            <option value="{{ $key }}" @selected($order->status === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="text-end"><a href="{{ route('orders.show', [$order, 'from' => 'dashboard']) }}" class="btn btn-outline-primary btn-sm">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty"><i class="fa-solid fa-inbox"></i>{{ $search !== '' ? 'Tidak ada pesanan yang cocok.' : 'Belum ada pesanan masuk.' }}</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
    </div>
@endsection
