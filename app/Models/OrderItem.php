<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotal(): string
    {
        return bcmul($this->price_snapshot, (string) $this->quantity, 2);
    }

    protected function productName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->product_snapshot['name'] ?? 'Unknown Product'
        );
    }

    protected function productImage(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->product_snapshot['primary_image'] ?? null
        );
    }
}
