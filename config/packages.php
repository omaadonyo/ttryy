<?php

return [
    'currency' => 'UGX',

    // Order copies go to the customer plus this admin inbox.
    'admin_email' => env('ADMIN_EMAIL', 'pay@ttryy.com'),

    // Durations (in months) a customer may spread payment over.
    // Full upfront payment always covers 12 months of the website running.
    'durations' => [3, 6, 12],

    // Domain options. The fee is a one-time payment, always due upfront
    // together with the first installment.
    'domains' => [
        'none' => ['label' => 'No domain — I already have one', 'fee' => 0],
        'budget' => ['label' => '.xyz / .online / .shop (one-time)', 'fee' => 29000],
        'premium' => ['label' => '.com / .org (one-time)', 'fee' => 80000],
    ],

    'frequencies' => ['full', 'monthly', 'weekly', 'daily'],

    'packages' => [
        'START' => [
            'price' => 250000,
            'deposit' => '100%',
            'monthly' => 21000,
            'daily' => 700,
            'weekly' => 4800,
            'blurb' => 'For businesses getting online and starting prospecting.',
        ],
        'GROW' => [
            'price' => 350000,
            'deposit' => '60%',
            'monthly' => 30000,
            'daily' => 1000,
            'weekly' => 6700,
            'blurb' => 'For businesses ready to actively pursue customers and opportunities.',
        ],
        'BUSINESS' => [
            'price' => 650000,
            'deposit' => '70%',
            'monthly' => 55000,
            'daily' => 1800,
            'weekly' => 12500,
            'blurb' => 'For businesses that want a larger business-development database.',
        ],
        'CORPORATE' => [
            'price' => 1400000,
            'deposit' => '50%',
            'monthly' => 117000,
            'daily' => 3800,
            'weekly' => 27000,
            'blurb' => 'For established companies that need everything, managed.',
        ],
    ],

    'niches' => [
        'NGOs & Charities',
        'Construction',
        'IT & Software',
        'Marketing & Advertising',
        'Cleaning & Facility Management',
        'Security',
        'Catering & Food Services',
        'Printing & Branding',
        'Office Furniture',
        'Accounting & Professional Services',
        'Logistics & Transport',
        'Agriculture & Agribusiness',
        'Solar & Renewable Energy',
        'Medical Suppliers',
        'Other / Not listed',
    ],
];
