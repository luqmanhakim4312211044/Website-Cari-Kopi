<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HandlesImages
{
    /**
     * Simpan gambar baru dan hapus yang lama.
     * Tanpa file baru, path lama dikembalikan apa adanya.
     */
    protected function storeImage(?UploadedFile $file, ?string $old = null): ?string
    {
        if (! $file) {
            return $old;
        }

        $this->deleteImage($old);

        return $file->store('gambarku', 'public');
    }

    protected function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
