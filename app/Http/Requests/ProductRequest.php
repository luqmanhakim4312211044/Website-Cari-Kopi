<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses dijaga middleware `can:kelola_toko` dan ProductPolicy
    }

    public function rules(): array
    {
        $user = $this->user();

        // Admin toko hanya boleh memasang produk di cafe miliknya; admin web di cafe mana pun.
        $postExists = Rule::exists('posts', 'id');
        if (! $user->isAdmin()) {
            $postExists = $postExists->where('user_id', $user->id);
        }

        return [
            'post_id' => ['required', $postExists],
            'title' => ['required', 'string', 'max:100'],
            'body' => ['required', 'string', 'max:1000'],
            'gambar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'price' => ['required', 'integer', 'min:0', 'max:100000000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'post_id' => 'cafe',
            'title' => 'nama produk',
            'body' => 'deskripsi',
            'price' => 'harga',
        ];
    }
}
