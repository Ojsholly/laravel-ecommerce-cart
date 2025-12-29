<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Low Stock Threshold
    |--------------------------------------------------------------------------
    |
    | The minimum stock quantity that triggers low stock notifications.
    | Products with stock at or below this threshold will be flagged.
    |
    */
    'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 10),

    /*
    |--------------------------------------------------------------------------
    | VAT Rate
    |--------------------------------------------------------------------------
    |
    | The default VAT (Value Added Tax) rate as a percentage.
    | This rate is applied to orders at checkout.
    |
    */
    'vat_rate' => env('VAT_RATE', 7.5),
];
