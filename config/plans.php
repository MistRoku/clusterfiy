<?php

return [
    'free' => [
        'label' => 'Free',
        'max_companies_per_user' => 1,
        'max_members_per_company' => 5,
        'excel_exports' => false,
    ],
    'team' => [
        'label' => 'Team',
        'max_companies_per_user' => null, // unlimited
        'max_members_per_company' => null, // unlimited
        'excel_exports' => true,
    ],
];
