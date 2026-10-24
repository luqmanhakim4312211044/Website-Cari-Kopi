<?php

namespace App\Models\Concerns;

trait HasImage
{
    /** URL gambar (kolom `gambar`), atau placeholder bila kosong. */
    public function getGambarUrlAttribute(): string
    {
        return $this->gambar
            ? asset('storage/'.$this->gambar)
            : asset('images/placeholder.svg');
    }
}
