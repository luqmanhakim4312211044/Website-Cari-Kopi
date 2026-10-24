@extends('layouts.app')

@section('title', 'Keranjang')

@section('content')
    <div class="page-head"><h1>Keranjang</h1></div>

    @if ($items->isEmpty())
        <div class="panel empty">
            <i class="fa-solid fa-bag-shopping"></i>
            Keranjangmu masih kosong.
            <div class="mt-3"><a href="{{ route('home') }}#daftar" class="btn btn-primary">Jelajahi cafe</a></div>
        </div>
    @else
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="panel overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-coffee">
                            <thead>
                                <tr><th>Produk</th><th>Harga</th><th style="width:10rem">Jumlah</th><th class="text-end">Subtotal</th><th><span class="visually-hidden">Aksi</span></th></tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img class="thumb-sm" src="{{ $item->product->gambar_url }}" alt="">
                                                <div>
                                                    <a href="{{ route('product.show', $item->product) }}" class="fw-semibold text-decoration-none">{{ $item->product->title }}</a>
                                                    <div class="small text-muted">{{ $item->product->post->title }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-nowrap">Rp {{ number_format($item->product->price, 0, ',', '.') }}</td>
                                        <td>
                                            <form action="{{ route('cart.update', $item) }}" method="POST">
                                                @csrf @method('PATCH')
                                                @include('partials.qty', [
                                                    'value' => $item->quantity,
                                                    'id' => 'qty'.$item->id,
                                                    'updateUrl' => route('cart.update', $item),
                                                    'itemId' => $item->id,
                                                ])
                                                <noscript><button class="btn btn-outline-primary btn-sm mt-2">Ubah</button></noscript>
                                            </form>
                                        </td>
                                        <td class="text-end text-nowrap fw-semibold" data-line-subtotal="{{ $item->id }}">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                        <td class="text-end">
                                            <form action="{{ route('cart.destroy', $item) }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-link text-danger p-0" aria-label="Hapus {{ $item->product->title }}"><i class="fa-regular fa-trash-can"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="panel panel-body">
                    <h2 class="h5 mb-3">Ringkasan</h2>
                    <div class="summary-row"><span>Subtotal</span><span id="sum-subtotal">Rp {{ number_format($totals['subtotal'], 0, ',', '.') }}</span></div>
                    @if ($totals['rate'] > 0)
                        <div class="summary-row text-success">
                            <span>Diskon member <span class="badge-soft badge-member">{{ $member }}</span></span>
                            <span id="sum-discount">&minus; Rp {{ number_format($totals['discount'], 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="summary-row summary-total"><span>Total</span><span id="sum-total">Rp {{ number_format($totals['total'], 0, ',', '.') }}</span></div>

                    @if ($groups->count() > 1)
                        <p class="small text-muted mt-3 mb-0"><i class="fa-solid fa-circle-info me-1"></i>Saat checkout, pesanan dipisah per cafe ({{ $groups->count() }} pesanan).</p>
                    @endif

                    <a href="{{ route('orders.create') }}" class="btn btn-primary w-100 mt-3">Checkout</a>
                    <a href="{{ route('home') }}#daftar" class="btn btn-link w-100 mt-1">Lanjut belanja</a>
                </div>
            </div>
        </div>
    @endif
@endsection
