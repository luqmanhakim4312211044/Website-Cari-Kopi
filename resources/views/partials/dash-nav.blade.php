{{-- Menu dashboard: dipakai di sidebar (desktop) dan offcanvas (layar kecil). Variabel: $pendingOrders --}}
<nav class="dash-nav" aria-label="Menu dashboard">
    <div class="dash-role">{{ auth()->user()->role_label }}</div>
    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fa-solid fa-gauge fa-fw"></i> Ringkasan
    </a>
    <a href="{{ route('dashboard.orders') }}" class="{{ request()->routeIs('dashboard.orders*') ? 'active' : '' }}">
        <i class="fa-solid fa-inbox fa-fw"></i> Pesanan masuk
        @if ($pendingOrders > 0)<span class="dash-badge">{{ $pendingOrders }}</span>@endif
    </a>
    <a href="{{ route('posts.index') }}" class="{{ request()->routeIs('posts.*') ? 'active' : '' }}">
        <i class="fa-solid fa-store fa-fw"></i> Cafe
    </a>
    <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.index', 'products.create', 'products.edit') ? 'active' : '' }}">
        <i class="fa-solid fa-mug-saucer fa-fw"></i> Produk
    </a>
    @can('kelola_web')
        <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <i class="fa-solid fa-tags fa-fw"></i> Kategori
        </a>
        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="fa-solid fa-users fa-fw"></i> Pengguna
        </a>
    @endcan
    <hr>
    <a href="{{ route('dashboard.report') }}" class="{{ request()->routeIs('dashboard.report*') ? 'active' : '' }}">
        <i class="fa-solid fa-chart-line fa-fw"></i> Laporan
    </a>
</nav>
