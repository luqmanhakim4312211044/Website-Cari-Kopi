@extends('layouts.dashboard')

@section('title', 'Laporan')

@section('panel')
    @php
        $rp = fn ($n) => 'Rp '.number_format((int) $n, 0, ',', '.');
        $all = $from === null;
        $pdfParams = $all ? ['all' => 1] : ['from' => $from->toDateString(), 'to' => $to->toDateString()];
        $presets = [
            'Bulan ini' => [],
            '30 hari terakhir' => ['from' => now()->subDays(29)->toDateString(), 'to' => now()->toDateString()],
            'Tahun ini' => ['from' => now()->startOfYear()->toDateString(), 'to' => now()->toDateString()],
            'Semua waktu' => ['all' => 1],
        ];
        $totals = $report['totals'];
    @endphp

    <div class="page-head">
        <div>
            <h1>Laporan penghasilan</h1>
            <span class="text-muted small">{{ $report['scope'] }} &middot; {{ $all ? 'Semua waktu' : $from->translatedFormat('d M Y').' – '.$to->translatedFormat('d M Y') }}</span>
        </div>
        <a href="{{ route('dashboard.report.pdf', $pdfParams) }}" class="btn btn-primary"><i class="fa-solid fa-file-pdf me-1"></i> Unduh PDF</a>
    </div>

    <div class="panel panel-body mb-4">
        <div class="d-flex flex-wrap gap-2 mb-3" aria-label="Rentang cepat">
            @foreach ($presets as $label => $params)
                <a href="{{ route('dashboard.report', $params) }}" class="chip">{{ $label }}</a>
            @endforeach
        </div>
        <form action="{{ route('dashboard.report') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-sm-4 col-md-3">
                <label for="from" class="form-label">Dari</label>
                <input type="date" id="from" name="from" value="{{ $from?->toDateString() }}" class="form-control">
            </div>
            <div class="col-sm-4 col-md-3">
                <label for="to" class="form-label">Sampai</label>
                <input type="date" id="to" name="to" value="{{ $to?->toDateString() }}" class="form-control">
            </div>
            <div class="col-sm-4 col-md-3"><button class="btn btn-outline-primary w-100">Tampilkan</button></div>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3"><div class="stat"><div class="num fs-4">{{ $rp($totals['revenue']) }}</div><div class="label">Pendapatan (sudah bayar)</div></div></div>
        <div class="col-6 col-xl-3"><div class="stat"><div class="num fs-4">{{ $rp($totals['pending_amount']) }}</div><div class="label">Menunggu pembayaran</div></div></div>
        <div class="col-6 col-xl-3"><div class="stat"><div class="num">{{ $totals['orders'] }}</div><div class="label">Pesanan ({{ $totals['paid_orders'] }} lunas, {{ $totals['pending_orders'] }} belum)</div></div></div>
        <div class="col-6 col-xl-3"><div class="stat"><div class="num fs-4">{{ $rp($totals['discount']) }}</div><div class="label">Diskon member diberikan</div></div></div>
    </div>

    <div class="row g-4">
        @if ($report['admin'])
            <div class="col-12">
                <div class="panel overflow-hidden">
                    <div class="panel-body pb-2"><h2 class="h5 mb-0">Pendapatan per toko</h2></div>
                    <div class="table-responsive">
                        <table class="table table-coffee">
                            <thead><tr><th>Toko</th><th class="text-center">Pesanan</th><th class="text-end">Pendapatan</th><th class="text-end">Menunggu bayar</th></tr></thead>
                            <tbody>
                                @forelse ($report['shops'] as $shop)
                                    <tr>
                                        <td class="fw-semibold">{{ $shop->seller }}</td>
                                        <td class="text-center">{{ $shop->orders }}</td>
                                        <td class="text-end text-nowrap">{{ $rp($shop->revenue) }}</td>
                                        <td class="text-end text-nowrap">{{ $rp($shop->pending) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4"><div class="empty"><i class="fa-solid fa-chart-line"></i>Belum ada pesanan pada periode ini.</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-12">
            <div class="panel overflow-hidden">
                <div class="panel-body pb-2"><h2 class="h5 mb-0">Produk terlaris <span class="text-muted fs-6 fw-normal">(pesanan sudah bayar)</span></h2></div>
                <div class="table-responsive">
                    <table class="table table-coffee">
                        <thead><tr><th>#</th><th>Produk</th><th class="text-center">Terjual</th><th class="text-end">Nilai penjualan</th></tr></thead>
                        <tbody>
                            @forelse ($report['products'] as $product)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="fw-semibold">{{ $product->nama }}</td>
                                    <td class="text-center">{{ $product->qty }}</td>
                                    <td class="text-end text-nowrap">{{ $rp($product->revenue) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><div class="empty"><i class="fa-solid fa-mug-saucer"></i>Belum ada penjualan yang sudah dibayar pada periode ini.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <p class="small text-muted mt-2 mb-0">Nilai penjualan per produk dihitung sebelum diskon member, jadi total barisnya bisa lebih besar dari pendapatan.</p>
        </div>
    </div>
@endsection
