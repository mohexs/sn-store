<?php

namespace App\Models;

use Database\Factories\OrderItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Monetary amounts use hundredths of a rupiah; discount_minor is the total discount for the line. */
#[Fillable(['order_id', 'product_id', 'product_name', 'product_sku', 'quantity', 'unit_price_minor', 'discount_minor'])]
class OrderItem extends Model
{
    /** @use HasFactory<OrderItemFactory> */
    use HasFactory;

    public const NET_EXPRESSION = '(order_items.quantity * order_items.unit_price_minor - order_items.discount_minor)';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function lineTotalMinor(): Attribute
    {
        return Attribute::make(get: fn (): int => $this->quantity * $this->unit_price_minor - $this->discount_minor);
    }

    protected function casts(): array
    {
        return ['quantity' => 'integer', 'processed_quantity' => 'integer', 'unit_price_minor' => 'integer', 'discount_minor' => 'integer'];
    }
}
