<?php

namespace App\Services\Pricing;

use App\Contracts\PricingPipeInterface;
use App\DataTransferObjects\PricingDTO;

class ApplyVATPipe implements PricingPipeInterface
{
    public function __construct(private ?float $vatRate = null) {}

    public function handle(PricingDTO $pricing, \Closure $next): PricingDTO
    {
        $vatRate = $this->vatRate ?? config('cart.vat_rate', 7.5);
        $vatDecimal = bcdiv((string) $vatRate, '100', 4);
        $vatAmount = bcmul($pricing->subtotal, $vatDecimal, 2);

        $pricing->addBreakdown('vat', $vatAmount, "VAT ({$vatRate}%)");
        $pricing->breakdown['vat']['rate'] = $vatRate;
        $pricing->total = bcadd($pricing->total, $vatAmount, 2);

        return $next($pricing);
    }
}
