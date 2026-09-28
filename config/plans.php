<?php

return [
    'free' => [
        'label' => 'Free',
        'price_monthly' => 0,
        'max_companies_per_user' => 1,
        'max_members_per_company' => 5,
        'excel_exports' => false,
        'stripe_price_id' => null,
    ],
    'team' => [
        'label' => 'Team',
        'price_monthly' => 12,
        'max_companies_per_user' => null, // unlimited
        'max_members_per_company' => null, // unlimited
        'excel_exports' => true,
        'stripe_price_id' => env('STRIPE_TEAM_PRICE_ID'),
    ],
];
