<?php

namespace App\Services\Admin;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function paginate(): LengthAwarePaginator
    {
        return Category::query()->withCount('products')->orderBy('name')->paginate(10);
    }

    public function create(array $data): Category
    {
        return Category::query()->create($data);
    }

    public function forEditing(Category $category): Category
    {
        return $category->loadCount('products');
    }

    public function update(Category $category, array $data): void
    {
        $category->update($data);
    }
}
