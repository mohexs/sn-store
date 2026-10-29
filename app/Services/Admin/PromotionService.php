<?php

namespace App\Services\Admin;

use App\Models\Promotion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PromotionService
{
    public function paginate(): LengthAwarePaginator
    {
        return Promotion::query()->orderBy('starts_at')->orderBy('id')->paginate(10);
    }

    public function create(array $data): Promotion
    {
        return Promotion::query()->create($data);
    }

    public function update(Promotion $promotion, array $data): void
    {
        $promotion->update($data);
    }

    public function delete(Promotion $promotion): void
    {
        $promotion->delete();
    }
}
