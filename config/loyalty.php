<?php

return [
    'tiers' => [
        'bronze' => [
            'min_spend' => 0,
            'discount' => 0,
            'multiplier' => 1,
        ],
        'silver' => [
            'min_spend' => 500000,
            'discount' => 0.05, // 5%
            'multiplier' => 1.2, // Poin x1.2
        ],
        'gold' => [
            'min_spend' => 2000000,
            'discount' => 0.10, // 10%
            'multiplier' => 1.5, // Poin x1.5
        ],
    ],
];
