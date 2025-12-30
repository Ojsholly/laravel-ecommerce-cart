<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'uuid',
        'order_number',
        'user_id',
        'subtotal',
        'vat_rate',
        'vat_amount',
        'total',
        'pricing_breakdown',
        'status',
    ];

    protected $attributes = [
        'status' => 'completed',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'vat_rate' => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'pricing_breakdown' => 'array',
            'status' => OrderStatus::class,
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

    public static function generateOrderNumber(): string
    {
        $maxAttempts = 10;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $date = date('Ymd');
            $randomSuffix = str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
            $orderNumber = 'ORD-'.$date.'-'.$randomSuffix;

            if (! static::where('order_number', $orderNumber)->exists()) {
                return $orderNumber;
            }
        }

        throw new \RuntimeException('Unable to generate unique order number after '.$maxAttempts.' attempts.');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::COMPLETED);
    }

    public function scopeForDate(Builder $query, $date): Builder
    {
        return $query->whereDate('created_at', $date);
    }

    public function markAsCompleted(): bool
    {
        return $this->update(['status' => OrderStatus::COMPLETED]);
    }

    public function markAsCancelled(): bool
    {
        return $this->update(['status' => OrderStatus::CANCELLED]);
    }
}
