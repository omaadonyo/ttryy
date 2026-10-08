<?php

return [

    // Token packs customers can buy to spend inside the platform.
    'packs' => [
        'starter' => ['tokens' => 100, 'price' => 5000, 'label' => 'Starter — 100 tokens'],
        'growth' => ['tokens' => 500, 'price' => 20000, 'label' => 'Growth — 500 tokens'],
        'pro' => ['tokens' => 2000, 'price' => 60000, 'label' => 'Pro — 2,000 tokens'],
    ],

    // Token price list. Amounts are deducted from the user's token balance.
    'costs' => [
        'ai_message' => 10,
        'group_unlock' => 10,
    ],
];
