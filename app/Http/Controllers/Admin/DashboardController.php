<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $products = collect(config('storefront.products', []));
        $productCount = $products->count();
        $categories = $products->countBy('category')
            ->sortDesc()
            ->map(fn (int $count, string $name): array => [
                'name' => $name,
                'count' => $count,
                'percentage' => round($count / max(1, $productCount) * 100),
            ])
            ->values();

        return view('pages.admin.dashboard.index', [
            'products' => $products->take(5),
            'productCount' => $productCount,
            'categories' => $categories,
            'categoryCount' => $categories->count(),
        ]);
    }
}
