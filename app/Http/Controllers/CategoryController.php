<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return view('categories.index', [
            'categories' => Category::withCount('posts')->orderBy('title')->get(),
        ]);
    }

    public function create()
    {
        return view('categories.create', ['category' => new Category()]);
    }

    public function store(Request $request)
    {
        Category::create($this->validated($request));

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dibuat.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request, $category));

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diubah.');
    }

    public function destroy(Category $category)
    {
        if ($category->posts()->exists()) {
            return back()->with('error', 'Kategori masih dipakai oleh cafe, tidak bisa dihapus.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:50', Rule::unique('categories', 'title')->ignore($category)],
        ]);
    }
}
