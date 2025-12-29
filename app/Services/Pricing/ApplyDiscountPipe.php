<?php

namespace App\Services\Pricing;

use App\Contracts\PricingPipeInterface;
use App\DataTransferObjects\PricingDTO;

class ApplyDiscountPipe implements PricingPipeInterface
{
    public function __construct(
        private string $discountAmount,
        private string $discountLabel = 'Discount'
    ) {}

    public function handle(PricingDTO $pricing, \Closure $next): PricingDTO
    {
        $discountAmount = bcmul($this->discountAmount, '-1', 2);

        $pricing->addBreakdown('discount', $discountAmount, $this->discountLabel);
        $pricing->total = bcadd($pricing->total, $discountAmount, 2);

        return $next($pricing);
    }
}
