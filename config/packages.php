<?php

return [
    'currency' => 'UGX',

    // Durations (in months) a customer may spread payment over.
    // Full upfront payment always covers 12 months of the website running.
    'durations' => [3, 6, 12, 24],

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
