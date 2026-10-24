<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses dijaga middleware `can:kelola_toko` dan PostPolicy
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:60'],
            'body' => ['required', 'string', 'max:1000'],
            'category_id' => ['required', 'exists:categories,id'],
            'gambar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kota' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string', 'max:255'],
        ];

        // Hanya admin web yang menentukan pemilik cafe; admin toko selalu menjadi pemilik cafenya sendiri.
        if ($this->user()->isAdmin()) {
            $rules['user_id'] = ['required', 'exists:users,id'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'title' => 'nama cafe',
            'body' => 'deskripsi',
            'category_id' => 'kategori',
            'user_id' => 'pemilik',
            'gambar' => 'gambar',
        ];
    }
}
