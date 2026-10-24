@extends('layouts.app')

@section('title', 'Pesanan #'.$order->id)

@section('content')
    <div class="page-head">
        <h1>Pesanan #{{ $order->id }} @include('partials.status-badge', ['order' => $order])</h1>
        <a href="{{ $backUrl }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> {{ $backLabel }}</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="panel overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-coffee">
                        <thead><tr><th>Produk</th><th class="text-end">Harga</th><th class="text-end">Jumlah</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td><div class="d-flex align-items-center gap-3"><img class="thumb-sm" src="{{ $item->gambar_url }}" alt="">{{ $item->nama }}</div></td>
                                    <td class="text-end text-nowrap">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                                    <td class="text-end">{{ $item->jumlah }}</td>
                                    <td class="text-end text-nowrap">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="panel-body border-top">
                    <div class="summary-row"><span>Subtotal</span><span>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</span></div>
                    @if ($order->diskon > 0)
                        <div class="summary-row text-success"><span>Diskon member</span><span>&minus; Rp {{ number_format($order->diskon, 0, ',', '.') }}</span></div>
                    @endif
                    <div class="summary-row summary-total"><span>Total</span><span>Rp {{ number_format($order->total, 0, ',', '.') }}</span></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel panel-body">
                <h2 class="h5 mb-3">Pengiriman</h2>
                <p class="tag mb-2"><i class="fa-solid fa-store me-1"></i>Penjual: {{ $order->seller?->name ?? '—' }}</p>
                <p class="mb-1 fw-semibold">{{ $order->nama }}</p>
                <p class="mb-1 text-muted">{{ $order->email }} &middot; {{ $order->nomor }}</p>
                <p class="mb-3">{{ $order->alamat }}</p>
                <p class="small text-muted mb-0">Dibuat {{ $order->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
                @if ($order->gambar)
                    <hr>
                    <p class="form-label">Bukti pembayaran</p>
                    <a href="{{ $order->gambar_url }}" target="_blank" rel="noopener"><img src="{{ $order->gambar_url }}" alt="Bukti pembayaran" class="img-fluid rounded-3"></a>
                @endif
            </div>
        </div>
    </div>
@endsection
