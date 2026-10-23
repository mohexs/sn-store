<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SalesReportRequest;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class SalesReportController extends Controller
{
    public function __invoke(SalesReportRequest $request): View
    {
        $filters = $request->validated();
        $sales = $this->salesQuery($filters);
        $summary = (clone $sales)->toBase()->selectRaw(
            'COUNT(DISTINCT orders.id) AS orders_count, COALESCE(SUM(order_items.quantity), 0) AS quantity, '
            .'COALESCE(SUM(order_items.quantity * order_items.unit_price_minor), 0) AS gross_minor, '
            .'COALESCE(SUM(order_items.discount_minor), 0) AS discount_minor, '
            .'COALESCE(SUM('.OrderItem::NET_EXPRESSION.'), 0) AS net_minor'
        )->first();

        $rows = match ($filters['type']) {
            'daily' => $sales->toBase()->selectRaw(
                'DATE(orders.sale_date) AS sale_date, COUNT(DISTINCT orders.id) AS orders_count, SUM(order_items.quantity) AS quantity, '
                .'SUM(order_items.quantity * order_items.unit_price_minor) AS gross_minor, '
                .'SUM(order_items.discount_minor) AS discount_minor, SUM('.OrderItem::NET_EXPRESSION.') AS net_minor'
            )->groupByRaw('DATE(orders.sale_date)')->orderByDesc('sale_date')->paginate(15),
            'products' => $sales->toBase()->selectRaw(
                'order_items.product_id, order_items.product_name, order_items.product_sku, '
                .'COUNT(DISTINCT orders.id) AS orders_count, SUM(order_items.quantity) AS quantity, '
                .'SUM(order_items.quantity * order_items.unit_price_minor) AS gross_minor, '
                .'SUM(order_items.discount_minor) AS discount_minor, SUM('.OrderItem::NET_EXPRESSION.') AS net_minor'
            )->groupBy('order_items.product_id', 'order_items.product_name', 'order_items.product_sku')
                ->orderBy(...match ($filters['sort'] ?? 'revenue') {
                    'quantity' => ['quantity', 'desc'],
                    'name' => ['order_items.product_name', 'asc'],
                    default => ['net_minor', 'desc'],
                })->orderBy('order_items.product_sku')->orderBy('order_items.product_id')->paginate(15),
            'orders' => Order::query()->whereIn('id', (clone $sales)->select('order_items.order_id'))
                ->with('items')->orderByDesc('sale_date')->orderByDesc('id')->paginate(10)
                ->through(function (Order $order): Order {
                    $order->setAttribute('net_minor', $order->items->sum('line_total_minor'));
                    $order->setAttribute('quantity', $order->items->sum('quantity'));

                    return $order;
                }),
        };

        return view('pages.admin.reports.index', [
            'filters' => $filters,
            'summary' => $summary,
            'rows' => $rows->appends($filters),
        ]);
    }

    /** @param array<string, mixed> $filters */
    private function salesQuery(array $filters): Builder
    {
        $query = OrderItem::query()->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'paid')
            ->where('orders.sale_date', '>=', $filters['start_date'])
            ->where('orders.sale_date', '<', Carbon::parse($filters['end_date'])->addDay()->toDateString());
        $keyword = trim($filters['q'] ?? '');

        if ($keyword !== '' && $filters['type'] !== 'daily') {
            $columns = $filters['type'] === 'products'
                ? ['order_items.product_name', 'order_items.product_sku']
                : ['orders.order_number', 'orders.customer_name'];
            $query->where(function (Builder $query) use ($columns, $keyword): void {
                $query->where($columns[0], 'like', '%'.$keyword.'%')->orWhere($columns[1], 'like', '%'.$keyword.'%');
            });
        }

        return $query;
    }
}
