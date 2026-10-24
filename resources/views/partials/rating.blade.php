@php
    $avg = (float) ($avg ?? 0);
    $count = (int) ($count ?? 0);
@endphp
<span class="rating" @if ($count) title="{{ number_format($avg, 1) }} dari 5" @endif>
    <span class="rating-stars" aria-hidden="true">
        @for ($i = 1; $i <= 5; $i++)
            @if ($avg >= $i - 0.25)
                <i class="fa-solid fa-star"></i>
            @elseif ($avg >= $i - 0.75)
                <i class="fa-solid fa-star-half-stroke"></i>
            @else
                <i class="fa-regular fa-star"></i>
            @endif
        @endfor
    </span>
    @if (isset($label) && ! $label)
        <span class="visually-hidden">{{ number_format($avg, 0) }} dari 5 bintang</span>
    @else
        <span class="rating-text">
            @if ($count)
                <strong>{{ number_format($avg, 1) }}</strong> <small>({{ $count }} ulasan)</small>
            @else
                <small>Belum ada ulasan</small>
            @endif
        </span>
    @endif
</span>
