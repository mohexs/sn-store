<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\Admin\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(CategoryService $service): View
    {
        return view('pages.admin.categories.index', [
            'categoryForForm' => new Category,
            'categories' => $service->paginate(),
        ]);
    }

    public function store(StoreCategoryRequest $request, CategoryService $service): RedirectResponse
    {
        $service->create($request->validated());

        return to_route('admin.categories.index')->with('status', 'Kategori berhasil ditambahkan. Kategori ini sudah tersedia pada form produk.');
    }

    public function edit(Category $category, CategoryService $service): View
    {
        return view('pages.admin.categories.edit', [
            'categoryForForm' => $service->forEditing($category),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category, CategoryService $service): RedirectResponse
    {
        $service->update($category, $request->validated());

        return to_route('admin.categories.index')->with('status', 'Kategori berhasil diperbarui. Perubahan juga berlaku pada produk yang terhubung.');
    }
}
