<?php

namespace App\Contracts;

use App\DataTransferObjects\PricingDTO;

interface PricingPipeInterface
{
    public function handle(PricingDTO $pricing, \Closure $next): PricingDTO;
}
