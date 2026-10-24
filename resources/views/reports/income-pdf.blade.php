@php
    $rp = fn ($n) => 'Rp '.number_format((int) $n, 0, ',', '.');
    $t = $report['totals'];
    $period = $report['from'] ? $report['from']->translatedFormat('d M Y').' - '.$report['to']->translatedFormat('d M Y') : 'Semua waktu';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Penghasilan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10.5px; color: #2a1b14; }
        h1 { font-size: 20px; margin: 0 0 2px; }
        h2 { font-size: 13px; margin: 20px 0 6px; }
        .muted { color: #7b6a5c; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #efe3d3; text-align: left; font-size: 9.5px; text-transform: uppercase; }
        th, td { border: 1px solid #d9c9b4; padding: 5px 7px; vertical-align: top; }
        .r { text-align: right; } .c { text-align: center; }
        .summary td { width: 25%; border: 1px solid #d9c9b4; background: #fffbf5; padding: 8px 10px; }
        .summary .v { font-size: 14px; font-weight: bold; margin-bottom: 2px; }
        .note { margin-top: 6px; font-size: 9px; color: #7b6a5c; }
    </style>
</head>
<body>
    <h1>Laporan Penghasilan</h1>
    <div class="muted">{{ $report['scope'] }} &middot; Periode: {{ $period }} &middot; Dicetak {{ now()->format('d-m-Y H:i') }} WIB</div>

    <h2>Ringkasan</h2>
    <table class="summary">
        <tr>
            <td><div class="v">{{ $rp($t['revenue']) }}</div>Pendapatan (sudah bayar)</td>
            <td><div class="v">{{ $rp($t['pending_amount']) }}</div>Menunggu pembayaran</td>
            <td><div class="v">{{ $t['orders'] }}</div>Pesanan ({{ $t['paid_orders'] }} lunas, {{ $t['pending_orders'] }} belum)</td>
            <td><div class="v">{{ $rp($t['discount']) }}</div>Diskon member diberikan</td>
        </tr>
    </table>

    @if ($report['admin'])
        <h2>Pendapatan per toko</h2>
        <table>
            <thead><tr><th>Toko</th><th class="c">Pesanan</th><th class="r">Pendapatan</th><th class="r">Menunggu bayar</th></tr></thead>
            <tbody>
                @forelse ($report['shops'] as $shop)
                    <tr><td>{{ $shop->seller }}</td><td class="c">{{ $shop->orders }}</td><td class="r">{{ $rp($shop->revenue) }}</td><td class="r">{{ $rp($shop->pending) }}</td></tr>
                @empty
                    <tr><td colspan="4" class="c muted">Belum ada pesanan pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <h2>Produk terlaris (pesanan sudah bayar)</h2>
    <table>
        <thead><tr><th>#</th><th>Produk</th><th class="c">Terjual</th><th class="r">Nilai penjualan</th></tr></thead>
        <tbody>
            @forelse ($report['products'] as $product)
                <tr><td>{{ $loop->iteration }}</td><td>{{ $product->nama }}</td><td class="c">{{ $product->qty }}</td><td class="r">{{ $rp($product->revenue) }}</td></tr>
            @empty
                <tr><td colspan="4" class="c muted">Belum ada penjualan yang sudah dibayar pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="note">Nilai penjualan per produk dihitung sebelum diskon member.</div>

    @unless ($report['admin'])
        <h2>Daftar pesanan masuk</h2>
        <table>
            <thead><tr><th>#</th><th>Tanggal</th><th>Pemesan</th><th>Item</th><th class="r">Total</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($report['orders'] as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>{{ $order->created_at->format('d-m-Y H:i') }} WIB</td>
                        <td>{{ $order->nama }}</td>
                        <td>@foreach ($order->items as $item){{ $item->nama }} &times; {{ $item->jumlah }}@if (! $loop->last), @endif @endforeach</td>
                        <td class="r">{{ $rp($order->total) }}</td>
                        <td>{{ $order->status_label }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="c muted">Belum ada pesanan pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($report['orders']->count() === 200)
            <div class="note">Menampilkan 200 pesanan terbaru.</div>
        @endif
    @endunless
</body>
</html>
