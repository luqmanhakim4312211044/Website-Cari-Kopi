{{--
    Pencarian + urutan terbaru/terlama untuk daftar di dashboard (hasil langsung tanpa reload).
    Variabel: $route, $search, $sort ('newest'|'oldest'), $placeholder,
              $hidden (opsional, mis. ['status' => 'paid']), $targets (opsional, default '#results')
--}}
<form action="{{ route($route) }}" method="GET" class="filter-bar mb-3" role="search" data-live data-live-target="{{ $targets ?? '#results' }}">
    @foreach (($hidden ?? []) as $key => $value)
        @if ($value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
    @endforeach

    @include('partials.search-pill', ['search' => $search, 'placeholder' => $placeholder ?? 'Cari...'])

    <label for="sort" class="visually-hidden">Urutkan</label>
    <select id="sort" name="sort" class="form-select" data-default="newest">
        <option value="newest" @selected($sort === 'newest')>Terbaru &rarr; Terlama</option>
        <option value="oldest" @selected($sort === 'oldest')>Terlama &rarr; Terbaru</option>
    </select>

    <button type="button" class="btn btn-outline-primary" data-live-reset @if ($search === '' && $sort === 'newest') hidden @endif>Reset</button>
</form>
