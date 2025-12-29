<?php

namespace App\DataTransferObjects;

class PricingDTO
{
    public function __construct(
        public string $subtotal = '0.00',
        public array $breakdown = [],
        public string $total = '0.00'
    ) {}

    public function addBreakdown(string $key, string $amount, string $label): self
    {
        $this->breakdown[$key] = [
            'amount' => $amount,
            'label' => $label,
        ];

        return $this;
    }

    public function toArray(): array
    {
        return [
            'subtotal' => $this->subtotal,
            'breakdown' => $this->breakdown,
            'total' => $this->total,
        ];
    }
}
