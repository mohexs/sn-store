<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.categories.index', [
            'categoryForForm' => new Category,
            'categories' => Category::query()->withCount('products')->orderBy('name')->paginate(10),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return to_route('admin.categories.index')->with('status', 'Kategori berhasil ditambahkan. Kategori ini sudah tersedia pada form produk.');
    }

    public function edit(Category $category): View
    {
        return view('pages.admin.categories.edit', [
            'categoryForForm' => $category->loadCount('products'),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return to_route('admin.categories.index')->with('status', 'Kategori berhasil diperbarui. Perubahan juga berlaku pada produk yang terhubung.');
    }
}
