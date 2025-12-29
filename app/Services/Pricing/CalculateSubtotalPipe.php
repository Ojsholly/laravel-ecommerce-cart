<?php

namespace App\Services\Pricing;

use App\Contracts\PricingPipeInterface;
use App\DataTransferObjects\PricingDTO;
use Illuminate\Support\Collection;

class CalculateSubtotalPipe implements PricingPipeInterface
{
    public function __construct(private Collection|array $items) {}

    public function handle(PricingDTO $pricing, \Closure $next): PricingDTO
    {
        $subtotal = '0';
        foreach ($this->items as $item) {
            $itemTotal = bcmul($item->product->price, (string) $item->quantity, 2);
            $subtotal = bcadd($subtotal, $itemTotal, 2);
        }

        $pricing->subtotal = $subtotal;
        $pricing->total = $subtotal;

        return $next($pricing);
    }
}
