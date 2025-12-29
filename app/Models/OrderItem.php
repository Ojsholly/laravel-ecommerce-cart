<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property int|null $product_id
 * @property int $quantity
 * @property string $price_snapshot
 * @property array<string, mixed> $product_snapshot
 * @property-read Order $order
 * @property-read Product|null $product
 * @property-read string $product_name
 * @property-read string|null $product_image
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price_snapshot',
        'product_snapshot',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price_snapshot' => 'decimal:2',
            'product_snapshot' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function getSubtotal(): string
    {
        return bcmul((string) $this->price_snapshot, (string) $this->quantity, 2);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function productName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => isset($this->product_snapshot['name'])
                ? (string) $this->product_snapshot['name']
                : 'Unknown Product'
        );
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function productImage(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => isset($this->product_snapshot['primary_image'])
                ? (string) $this->product_snapshot['primary_image']
                : null
        );
    }
}
