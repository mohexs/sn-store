<?php

namespace App\Services;

use App\Models\Promotion;
use Illuminate\Support\Collection;

class CheckoutSummaryService
{
    /**
     * @param  array<int, array{id: int, quantity: int}>  $cart
     * @return array{
     *     lines: Collection<int, array<string, int|string>>,
     *     subtotal_minor: int,
     *     discount_minor: int,
     *     total_minor: int,
     *     promotion: Promotion|null
     * }
     */
    public function summarize(array $cart): array
    {
        $products = collect(config('storefront.products'))->keyBy('id');
        $lines = collect($cart)->map(function (array $item) use ($products): array {
            $product = $products->get($item['id']);

            return [
                'id' => (int) $product['id'],
                'name' => (string) $product['name'],
                'sku' => 'DEMO-'.str_pad((string) $product['id'], 6, '0', STR_PAD_LEFT),
                'price' => (int) $product['price'],
                'quantity' => $item['quantity'],
                'line_total_minor' => (int) $product['price'] * $item['quantity'] * 100,
                'discount_minor' => 0,
            ];
        });

        $subtotalMinor = (int) $lines->sum('line_total_minor');
        $promotion = Promotion::query()
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->orderBy('id')
            ->get()
            ->map(fn (Promotion $promotion): array => [
                'promotion' => $promotion,
                'discount_minor' => min($subtotalMinor, $this->discountFor($promotion, $subtotalMinor)),
            ])
            ->sortByDesc('discount_minor')
            ->first();
        $promotionModel = $promotion['promotion'] ?? null;
        $discountMinor = $promotion['discount_minor'] ?? 0;
        $remainingDiscount = $discountMinor;
        $lineCount = $lines->count();

        $lines = $lines->values()->map(function (array $line, int $index) use (
            $subtotalMinor,
            $discountMinor,
            &$remainingDiscount,
            $lineCount,
        ): array {
            $lineDiscount = $index === $lineCount - 1
                ? min($line['line_total_minor'], $remainingDiscount)
                : min($line['line_total_minor'], intdiv($line['line_total_minor'] * $discountMinor, max(1, $subtotalMinor)));
            $remainingDiscount -= $lineDiscount;

            return [...$line, 'discount_minor' => $lineDiscount];
        });

        return [
            'lines' => $lines,
            'subtotal_minor' => $subtotalMinor,
            'discount_minor' => $discountMinor,
            'total_minor' => $subtotalMinor - $discountMinor,
            'promotion' => $promotionModel,
        ];
    }

    private function discountFor(Promotion $promotion, int $subtotalMinor): int
    {
        return $promotion->discount_type === 'percentage'
            ? (int) round($subtotalMinor * (float) $promotion->discount_value / 100)
            : (int) round((float) $promotion->discount_value * 100);
    }
}
