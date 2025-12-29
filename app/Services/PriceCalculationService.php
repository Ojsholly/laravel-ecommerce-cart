<?php

namespace App\Services;

use App\DataTransferObjects\PricingDTO;
use App\Services\Pricing\ApplyVATPipe;
use App\Services\Pricing\CalculateSubtotalPipe;
use Illuminate\Pipeline\Pipeline;

class PriceCalculationService
{
    public function calculateOrderPricing(array $items, ?float $vatRate = null): PricingDTO
    {
        $pipes = [
            new CalculateSubtotalPipe($items),
            new ApplyVATPipe($vatRate),
        ];

        return app(Pipeline::class)
            ->send(new PricingDTO)
            ->through($pipes)
            ->thenReturn();
    }

    public function formatPrice(string $price): string
    {
        return '$'.number_format((float) $price, 2);
    }
}
