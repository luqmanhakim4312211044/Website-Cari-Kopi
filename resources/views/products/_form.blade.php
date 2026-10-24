@php
    $field = fn (string $name) => old($name, $product->{$name});
@endphp
<div class="row g-3">
    <div class="col-md-6">
        <label for="post_id" class="form-label">Cafe</label>
        <select id="post_id" name="post_id" class="form-select @error('post_id') is-invalid @enderror" required>
            <option value="">— Pilih cafe —</option>
            @foreach ($posts as $post)
                <option value="{{ $post->id }}" @selected($field('post_id') == $post->id)>{{ $post->title }}@can('kelola_web') — {{ $post->user->name }}@endcan</option>
            @endforeach
        </select>
        @if ($posts->isEmpty())<div class="form-text">Kamu belum punya cafe. <a href="{{ route('posts.create') }}">Tambah cafe</a> dulu.</div>@endif
    </div>
    <div class="col-md-6">
        <label for="price" class="form-label">Harga (Rp)</label>
        <input type="number" id="price" name="price" min="0" step="500" value="{{ $field('price') }}" class="form-control @error('price') is-invalid @enderror" required>
    </div>
    <div class="col-12">
        <label for="title" class="form-label">Nama produk</label>
        <input type="text" id="title" name="title" value="{{ $field('title') }}" maxlength="100" class="form-control @error('title') is-invalid @enderror" required>
    </div>
    <div class="col-12">
        <label for="body" class="form-label">Deskripsi</label>
        <textarea id="body" name="body" rows="3" maxlength="1000" class="form-control @error('body') is-invalid @enderror" required>{{ $field('body') }}</textarea>
    </div>
    <div class="col-12">
        <label for="gambar" class="form-label">Gambar</label>
        <input type="file" id="gambar" name="gambar" accept="image/*" class="form-control @error('gambar') is-invalid @enderror">
        @if ($product->exists && $product->gambar)
            <div class="d-flex align-items-center gap-2 mt-2"><img class="thumb-sm" src="{{ $product->gambar_url }}" alt=""><span class="form-text m-0">Kosongkan jika tidak ingin mengganti gambar.</span></div>
        @endif
    </div>
</div>
<div class="mt-4">
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ route('products.index') }}" class="btn btn-link">Batal</a>
</div>
