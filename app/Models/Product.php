<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $description
 * @property string $price
 * @property int $stock_quantity
 * @property array<int, array<string, mixed>> $images
 * @property-read array<string, mixed>|null $primary_image
 * @property-read array<int, array<string, mixed>> $sorted_images
 */
class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'uuid',
        'name',
        'description',
        'price',
        'stock_quantity',
        'images',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'stock_quantity' => 'integer',
        ];
    }

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value,
            set: fn ($value) => bcmul($value, '1', 2)
        );
    }

    /**
     * @return Attribute<array<string, mixed>|null, never>
     */
    protected function primaryImage(): Attribute
    {
        return Attribute::make(
            get: function (): ?array {
                if (empty($this->images)) {
                    return null;
                }
                $primary = collect($this->images)->firstWhere('is_primary', true);

                return is_array($primary) ? $primary : ($this->images[0] ?? null);
            }
        );
    }

    /**
     * @return Attribute<array<int, array<string, mixed>>, never>
     */
    protected function sortedImages(): Attribute
    {
        return Attribute::make(
            get: function (): array {
                if (empty($this->images)) {
                    return [];
                }

                return collect($this->images)
                    ->sortBy('order')
                    ->values()
                    ->toArray();
            }
        );
    }

    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock_quantity >= $quantity;
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity > 0 && $this->stock_quantity <= config('cart.low_stock_threshold', 10);
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0)
            ->where('stock_quantity', '<=', config('cart.low_stock_threshold', 10));
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function toSnapshot(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'images' => $this->images,
            'primary_image' => $this->primary_image,
        ];
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
