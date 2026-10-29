<?php

namespace App\Services\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesReportService
{
    /**
     * @param  array{type: string, start_date: string, end_date: string, q?: string|null, sort?: string|null}  $filters
     * @return array{summary: object, rows: LengthAwarePaginator}
     */
    public function report(array $filters): array
    {
        $sales = $this->salesQuery($filters);
        $summary = (clone $sales)->toBase()->selectRaw(
            'COUNT(DISTINCT orders.id) AS orders_count, COALESCE(SUM(order_items.quantity), 0) AS quantity, '
            .'COALESCE(SUM(order_items.quantity * order_items.unit_price_minor), 0) AS gross_minor, '
            .'COALESCE(SUM(order_items.discount_minor), 0) AS discount_minor, '
            .'COALESCE(SUM('.OrderItem::NET_EXPRESSION.'), 0) AS net_minor'
        )->first();

        $rows = match ($filters['type']) {
            'daily' => $this->dailyRowsQuery($sales)->paginate(15),
            'products' => $this->productRowsQuery($sales, $filters['sort'] ?? 'revenue')->paginate(15),
            'orders' => $this->ordersQuery($sales)->paginate(10)
                ->through(fn (Order $order): Order => $this->addOrderTotals($order)),
        };

        return ['summary' => $summary, 'rows' => $rows];
    }

    /**
     * @param  array{type: string, start_date: string, end_date: string, q?: string|null, sort?: string|null}  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        $type = $filters['type'];
        $filename = sprintf('laporan-%s-%s-sampai-%s.csv', $type, $filters['start_date'], $filters['end_date']);

        return response()->streamDownload(function () use ($filters, $type): void {
            $output = fopen('php://output', 'wb');

            if ($output === false) {
                throw new RuntimeException('Tidak dapat membuka aliran untuk ekspor laporan.');
            }

            try {
                if (fwrite($output, "\xEF\xBB\xBF") === false) {
                    throw new RuntimeException('Tidak dapat menulis penanda UTF-8 untuk ekspor laporan.');
                }

                switch ($type) {
                    case 'daily':
                        $this->writeDailyExport($output, $filters);
                        break;
                    case 'products':
                        $this->writeProductExport($output, $filters);
                        break;
                    case 'orders':
                        $this->writeOrderExport($output, $filters);
                        break;
                }
            } finally {
                fclose($output);
            }
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @param  array{type: string, start_date: string, end_date: string, q?: string|null}  $filters
     */
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

    private function dailyRowsQuery(Builder $sales): QueryBuilder
    {
        return $sales->toBase()->selectRaw(
            'DATE(orders.sale_date) AS sale_date, COUNT(DISTINCT orders.id) AS orders_count, SUM(order_items.quantity) AS quantity, '
            .'SUM(order_items.quantity * order_items.unit_price_minor) AS gross_minor, '
            .'SUM(order_items.discount_minor) AS discount_minor, SUM('.OrderItem::NET_EXPRESSION.') AS net_minor'
        )->groupByRaw('DATE(orders.sale_date)')->orderByDesc('sale_date');
    }

    private function productRowsQuery(Builder $sales, string $sort): QueryBuilder
    {
        return $sales->toBase()->selectRaw(
            'order_items.product_id, order_items.product_name, order_items.product_sku, '
            .'COUNT(DISTINCT orders.id) AS orders_count, SUM(order_items.quantity) AS quantity, '
            .'SUM(order_items.quantity * order_items.unit_price_minor) AS gross_minor, '
            .'SUM(order_items.discount_minor) AS discount_minor, SUM('.OrderItem::NET_EXPRESSION.') AS net_minor'
        )->groupBy('order_items.product_id', 'order_items.product_name', 'order_items.product_sku')
            ->orderBy(...match ($sort) {
                'quantity' => ['quantity', 'desc'],
                'name' => ['order_items.product_name', 'asc'],
                default => ['net_minor', 'desc'],
            })->orderBy('order_items.product_sku')->orderBy('order_items.product_id');
    }

    private function ordersQuery(Builder $sales): Builder
    {
        return Order::query()->whereIn('id', (clone $sales)->select('order_items.order_id'))
            ->with('items')->orderByDesc('sale_date')->orderByDesc('id');
    }

    private function addOrderTotals(Order $order): Order
    {
        $order->setAttribute('net_minor', $order->items->sum('line_total_minor'));
        $order->setAttribute('quantity', $order->items->sum('quantity'));

        return $order;
    }

    /**
     * @param  resource  $output
     * @param  array{type: string, start_date: string, end_date: string, q?: string|null, sort?: string|null}  $filters
     */
    private function writeDailyExport($output, array $filters): void
    {
        $this->writeCsvRow($output, ['tanggal', 'jumlah_pesanan', 'unit_terjual', 'penjualan_kotor_rupiah', 'diskon_rupiah', 'penjualan_bersih_rupiah']);

        $this->dailyRowsQuery($this->salesQuery($filters))->lazy(500)->each(fn (object $row) => $this->writeCsvRow($output, [
            $row->sale_date,
            $row->orders_count,
            $row->quantity,
            $this->rupiah($row->gross_minor),
            $this->rupiah($row->discount_minor),
            $this->rupiah($row->net_minor),
        ]));
    }

    /**
     * @param  resource  $output
     * @param  array{type: string, start_date: string, end_date: string, q?: string|null, sort?: string|null}  $filters
     */
    private function writeProductExport($output, array $filters): void
    {
        $this->writeCsvRow($output, ['nama_produk', 'sku_saat_transaksi', 'jumlah_pesanan', 'unit_terjual', 'penjualan_kotor_rupiah', 'diskon_rupiah', 'penjualan_bersih_rupiah']);

        $this->productRowsQuery($this->salesQuery($filters), $filters['sort'] ?? 'revenue')
            ->lazy(500)
            ->each(fn (object $row) => $this->writeCsvRow($output, [
                $row->product_name,
                $row->product_sku,
                $row->orders_count,
                $row->quantity,
                $this->rupiah($row->gross_minor),
                $this->rupiah($row->discount_minor),
                $this->rupiah($row->net_minor),
            ]));
    }

    /**
     * @param  resource  $output
     * @param  array{type: string, start_date: string, end_date: string, q?: string|null, sort?: string|null}  $filters
     */
    private function writeOrderExport($output, array $filters): void
    {
        $this->writeCsvRow($output, [
            'nomor_pesanan', 'tanggal', 'nama_pelanggan', 'total_pesanan_bersih_rupiah', 'total_unit_pesanan',
            'nama_produk', 'sku_saat_transaksi', 'harga_satuan_rupiah', 'jumlah', 'diskon_baris_rupiah', 'subtotal_bersih_baris_rupiah',
        ]);

        $this->ordersQuery($this->salesQuery($filters))->lazy(200)->each(function (Order $order) use ($output): void {
            $this->addOrderTotals($order);

            foreach ($order->items as $item) {
                $this->writeCsvRow($output, [
                    $order->order_number,
                    $order->sale_date?->toDateString(),
                    $order->customer_name ?? 'Pelanggan tanpa nama',
                    $this->rupiah($order->net_minor),
                    $order->quantity,
                    $item->product_name,
                    $item->product_sku,
                    $this->rupiah($item->unit_price_minor),
                    $item->quantity,
                    $this->rupiah($item->discount_minor),
                    $this->rupiah($item->line_total_minor),
                ]);
            }
        });
    }

    /**
     * @param  resource  $output
     * @param  array<int, mixed>  $row
     */
    private function writeCsvRow($output, array $row): void
    {
        $row = array_map(function (mixed $value): string {
            $cell = $value === null ? '' : (string) $value;

            return preg_match('/\A[\x00-\x20]*[=+\-@]/', $cell) === 1 ? "'".$cell : $cell;
        }, $row);

        if (fputcsv($output, $row, ',', '"', '', "\r\n") === false) {
            throw new RuntimeException('Tidak dapat menulis baris ekspor laporan.');
        }
    }

    private function rupiah(int|string $minor): string
    {
        $amount = (int) $minor;

        return intdiv($amount, 100).'.'.str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }
}
