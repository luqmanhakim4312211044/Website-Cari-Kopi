{{-- Kolom pencarian. Hasil muncul saat mengetik (lihat data-live di form pemakainya). Variabel: $search, $placeholder --}}
<div class="search-pill">
    <label for="search" class="search-icon">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <span class="visually-hidden">Cari</span>
    </label>
    <input id="search" type="search" name="search" value="{{ $search }}" placeholder="{{ $placeholder ?? 'Cari...' }}" autocomplete="off">
</div>
