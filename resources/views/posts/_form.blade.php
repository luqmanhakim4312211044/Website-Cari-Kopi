@php
    $field = fn (string $name) => old($name, $post->{$name});
    $isAdmin = auth()->user()->isAdmin();
@endphp
<div class="row g-3">
    <div class="{{ $isAdmin ? 'col-md-5' : 'col-md-8' }}">
        <label for="title" class="form-label">Nama cafe</label>
        <input type="text" id="title" name="title" value="{{ $field('title') }}" maxlength="60" class="form-control @error('title') is-invalid @enderror" required>
    </div>
    <div class="{{ $isAdmin ? 'col-md-3' : 'col-md-4' }}">
        <label for="category_id" class="form-label">Kategori</label>
        <select id="category_id" name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">— Pilih —</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected($field('category_id') == $category->id)>{{ $category->title }}</option>
            @endforeach
        </select>
        @if ($categories->isEmpty())<div class="form-text">Belum ada kategori. <a href="{{ route('categories.create') }}">Buat dulu</a>.</div>@endif
    </div>
    @can('kelola_web')
        <div class="col-md-4">
            <label for="user_id" class="form-label">Pemilik toko</label>
            <select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                @foreach ($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((int) $field('user_id') === (int) $owner->id || (! $post->exists && ! old('user_id') && (int) $owner->id === (int) auth()->id()))>
                        {{ $owner->name }} ({{ $owner->email }})
                    </option>
                @endforeach
            </select>
            <div class="form-text">Pemilik menerima pesanan dari produk di cafe ini.</div>
        </div>
    @endcan
    <div class="col-12">
        <label for="body" class="form-label">Deskripsi</label>
        <textarea id="body" name="body" rows="4" maxlength="1000" class="form-control @error('body') is-invalid @enderror" required>{{ $field('body') }}</textarea>
    </div>
    <div class="col-12">
        <label for="gambar" class="form-label">Gambar</label>
        <input type="file" id="gambar" name="gambar" accept="image/*" class="form-control @error('gambar') is-invalid @enderror">
        @if ($post->exists && $post->gambar)
            <div class="d-flex align-items-center gap-2 mt-2"><img class="thumb-sm" src="{{ $post->gambar_url }}" alt=""><span class="form-text m-0">Kosongkan jika tidak ingin mengganti gambar.</span></div>
        @endif
    </div>
    <div class="col-md-6">
        <label for="latitude" class="form-label">Latitude</label>
        <input type="number" step="any" id="latitude" name="latitude" value="{{ $field('latitude') }}" class="form-control @error('latitude') is-invalid @enderror" placeholder="contoh: 1.1301" required>
    </div>
    <div class="col-md-6">
        <label for="longitude" class="form-label">Longitude</label>
        <input type="number" step="any" id="longitude" name="longitude" value="{{ $field('longitude') }}" class="form-control @error('longitude') is-invalid @enderror" placeholder="contoh: 104.0530" required>
        <div class="form-text">Salin koordinat dari Google Maps (klik kanan pada lokasi).</div>
    </div>
    <div class="col-md-6">
        <label for="provinsi" class="form-label">Provinsi</label>
        <input type="text" id="provinsi" name="provinsi" value="{{ $field('provinsi') }}" class="form-control @error('provinsi') is-invalid @enderror" required>
    </div>
    <div class="col-md-6">
        <label for="kota" class="form-label">Kota</label>
        <input type="text" id="kota" name="kota" value="{{ $field('kota') }}" class="form-control @error('kota') is-invalid @enderror" required>
    </div>
    <div class="col-12">
        <label for="alamat" class="form-label">Alamat</label>
        <input type="text" id="alamat" name="alamat" value="{{ $field('alamat') }}" class="form-control @error('alamat') is-invalid @enderror" required>
    </div>
</div>
<div class="mt-4">
    <button class="btn btn-primary">Simpan</button>
    <a href="{{ route('posts.index') }}" class="btn btn-link">Batal</a>
</div>
