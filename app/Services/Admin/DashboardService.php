<?php

namespace App\Services\Admin;

use App\Models\Order;
use Illuminate\Support\Carbon;

class DashboardService
{
    /**
     * @param  array{start_date: string, end_date: string}  $filters
     * @return array<string, mixed>
     */
    public function data(array $filters): array
    {
        $ordersInPeriod = Order::query()
            ->where('sale_date', '>=', $filters['start_date'])
            ->where('sale_date', '<', Carbon::parse($filters['end_date'])->addDay()->toDateString());
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

        return [
            'products' => $products->take(5),
            'productCount' => $productCount,
            'categories' => $categories,
            'categoryCount' => $categories->count(),
            'filters' => $filters,
            'orderCount' => (clone $ordersInPeriod)->count(),
            'revenueMinor' => (clone $ordersInPeriod)->where('status', 'paid')->sum('total_minor'),
        ];
    }
}
