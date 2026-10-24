<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/** Pencarian & urutan waktu yang sama untuk daftar di dashboard. */
trait FiltersLists
{
    /** @return array{0: string, 1: string} [kata kunci, 'newest'|'oldest'] */
    protected function listFilters(Request $request): array
    {
        $search = trim((string) $request->query('search'));
        $sort = $request->query('sort') === 'oldest' ? 'oldest' : 'newest';

        return [$search, $sort];
    }

    /** Pola LIKE dengan karakter khusus (% _ \) di-escape. */
    protected function like(string $search): string
    {
        return '%'.addcslashes($search, '%_\\').'%';
    }

    /** Urut berdasarkan waktu dibuat. $query boleh Builder maupun relasi. */
    protected function applySort($query, string $sort)
    {
        $direction = $sort === 'oldest' ? 'asc' : 'desc';

        return $query->orderBy('created_at', $direction)->orderBy('id', $direction);
    }
}
