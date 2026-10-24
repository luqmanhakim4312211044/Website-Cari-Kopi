{{--
    Input jumlah dengan tombol − / +.
    Opsional: $id, $value (default 1), $large (bool), $updateUrl + $itemId (untuk update otomatis di keranjang)
--}}
@php
    $value = $value ?? 1;
    $large = $large ?? false;
@endphp
<div class="qty {{ $large ? 'qty-lg' : '' }}" data-qty>
    <button type="button" class="qty-btn" data-qty-step="-1" aria-label="Kurangi jumlah">&minus;</button>
    <input type="number" name="quantity" value="{{ $value }}" min="1" max="99" inputmode="numeric" aria-label="Jumlah"
           @isset($id) id="{{ $id }}" @endisset
           @isset($updateUrl) data-update-url="{{ $updateUrl }}" data-item-id="{{ $itemId }}" @endisset>
    <button type="button" class="qty-btn" data-qty-step="1" aria-label="Tambah jumlah">+</button>
</div>
