@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <div class="page-head"><h1>Checkout</h1></div>

    <div class="row g-4">
        <div class="col-lg-7">
            <form action="{{ route('orders.store') }}" method="POST" enctype="multipart/form-data" class="panel panel-body">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" class="form-control" value="{{ $user->name }} ({{ $user->email }})" disabled>
                </div>
                <div class="mb-3">
                    <label for="nomor" class="form-label">Nomor telepon</label>
                    <input type="tel" id="nomor" name="nomor" value="{{ old('nomor') }}" class="form-control @error('nomor') is-invalid @enderror" required>
                </div>
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat pengiriman</label>
                    <input type="text" id="alamat" name="alamat" value="{{ old('alamat') }}" class="form-control @error('alamat') is-invalid @enderror" required>
                </div>
                <div class="mb-4">
                    <label for="gambar" class="form-label">Bukti pembayaran <span class="text-muted fw-normal">(opsional)</span></label>
                    <input type="file" id="gambar" name="gambar" accept="image/*" class="form-control @error('gambar') is-invalid @enderror">
                    <div class="form-text">JPG, PNG, atau WebP, maksimal 2 MB.</div>
                </div>
                <button class="btn btn-primary">Buat pesanan</button>
                <a href="{{ route('cart') }}" class="btn btn-link">Kembali ke keranjang</a>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="panel panel-body">
                <h2 class="h5 mb-3">Pesananmu</h2>

                @foreach ($groups as $group)
                    <div class="mb-3">
                        <div class="tag mb-1"><i class="fa-solid fa-store me-1"></i>{{ $group['seller']->name }}</div>
                        @foreach ($group['items'] as $item)
                            <div class="summary-row">
                                <span>{{ $item->product->title }} <span class="text-muted">&times; {{ $item->quantity }}</span></span>
                                <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endforeach

                <hr>
                <div class="summary-row"><span>Subtotal</span><span>Rp {{ number_format($totals['subtotal'], 0, ',', '.') }}</span></div>
                @if ($totals['discount'] > 0)
                    <div class="summary-row text-success"><span>Diskon member</span><span>&minus; Rp {{ number_format($totals['discount'], 0, ',', '.') }}</span></div>
                @endif
                <div class="summary-row summary-total"><span>Total</span><span>Rp {{ number_format($totals['total'], 0, ',', '.') }}</span></div>

                @if ($groups->count() > 1)
                    <p class="small text-muted mt-3 mb-0"><i class="fa-solid fa-circle-info me-1"></i>Dipisah menjadi {{ $groups->count() }} pesanan, satu untuk tiap penjual.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
