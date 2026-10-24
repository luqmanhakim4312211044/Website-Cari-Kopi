<div class="mb-4">
    <label for="title" class="form-label">Nama kategori</label>
    <input type="text" id="title" name="title" value="{{ old('title', $category->title) }}" maxlength="50" class="form-control @error('title') is-invalid @enderror" required>
</div>
<button class="btn btn-primary">Simpan</button>
<a href="{{ route('categories.index') }}" class="btn btn-link">Batal</a>
