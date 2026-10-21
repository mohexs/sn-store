<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;

class UpdateCategoryRequest extends StoreCategoryRequest
{
    protected function categoryToIgnore(): ?Category
    {
        return $this->route('category');
    }
}
