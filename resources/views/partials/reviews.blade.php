{{--
    Bagian ulasan yang dipakai halaman cafe & produk.
    Variabel: $reviews (koleksi, relasi user sudah di-load), $storeRoute (URL untuk ulasan baru)
--}}
@php
    $myReview = auth()->check() ? $reviews->firstWhere('user_id', auth()->id()) : null;
@endphp

<section id="ulasan" class="mt-5">
    <h2 class="section-title">Ulasan</h2>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="panel panel-body">
                @guest
                    <p class="mb-3">Masuk dulu untuk menulis ulasan.</p>
                    <a href="{{ route('login') }}" class="btn btn-primary">Masuk</a>
                @else
                    <h3 class="h5 mb-3">{{ $myReview ? 'Ulasanmu' : 'Tulis ulasan' }}</h3>

                    <form action="{{ $myReview ? route('reviews.update', $myReview) : $storeRoute }}" method="POST">
                        @csrf
                        @if ($myReview) @method('PATCH') @endif

                        @php
                            $currentRating = (int) old('rating', $myReview->rating ?? 5);
                        @endphp
                        <fieldset class="mb-3">
                            <legend class="form-label">Rating</legend>
                            <div class="rate-input">
                                @foreach ([5, 4, 3, 2, 1] as $star)
                                    <input type="radio" name="rating" id="rate{{ $star }}" value="{{ $star }}" @checked($currentRating === $star)>
                                    <label for="rate{{ $star }}" title="{{ $star }} bintang"><i class="fa-solid fa-star"></i><span class="visually-hidden">{{ $star }} bintang</span></label>
                                @endforeach
                            </div>
                        </fieldset>

                        <div class="mb-3">
                            <label for="comment" class="form-label">Komentar <span class="text-muted fw-normal">(opsional)</span></label>
                            <textarea id="comment" name="comment" rows="4" maxlength="500" class="form-control">{{ old('comment', $myReview->comment ?? '') }}</textarea>
                        </div>

                        <button class="btn btn-primary">{{ $myReview ? 'Simpan perubahan' : 'Kirim ulasan' }}</button>
                    </form>
                @endguest
            </div>
        </div>

        <div class="col-lg-7">
            <div class="panel panel-body">
                @forelse ($reviews as $review)
                    <article class="review d-flex gap-3">
                        <div class="avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($review->user->name, 0, 1)) }}</div>
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap justify-content-between gap-2">
                                <strong>{{ $review->user->name }}</strong>
                                <small class="text-muted">{{ $review->created_at->diffForHumans() }}</small>
                            </div>
                            @include('partials.rating', ['avg' => $review->rating, 'count' => 1, 'label' => false])
                            @if ($review->comment)
                                <p class="mt-2 mb-1">{{ $review->comment }}</p>
                            @endif
                            @can('delete', $review)
                                <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="d-inline"
                                      data-confirm="Hapus ulasan ini?">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-link btn-sm text-danger p-0">Hapus</button>
                                </form>
                            @endcan
                        </div>
                    </article>
                @empty
                    <div class="empty"><i class="fa-regular fa-comments"></i>Belum ada ulasan. Jadilah yang pertama!</div>
                @endforelse
            </div>
        </div>
    </div>
</section>
