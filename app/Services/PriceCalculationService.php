<?php

namespace App\Services;

use App\DataTransferObjects\PricingDTO;
use App\Services\Pricing\ApplyVATPipe;
use App\Services\Pricing\CalculateSubtotalPipe;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Collection;

/**
 * Service for calculating prices with VAT using a pipeline pattern.
 */
class PriceCalculationService
{
    /**
     * Calculate order pricing including VAT using pipeline pattern.
     */
    public function calculateOrderPricing(Collection|array $items, ?float $vatRate = null): PricingDTO
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
