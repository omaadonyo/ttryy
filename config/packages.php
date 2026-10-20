<?php

return [
    'currency' => 'UGX',

    // Launch 10K campaign fee: build setup + free subdomain + first 10 days.
    'launch_fee' => 10000,

    // Order copies go to the customer plus this admin inbox.
    'admin_email' => env('ADMIN_EMAIL', 'pay@ttryy.com'),

    // Durations (in months) a customer may spread payment over.
    // Full upfront payment always covers 12 months of the website running.
    'durations' => [3, 6, 12],

    // Minimum first payment. The customer must pay at least this much, and
    // enough to cover `min_first_days` at the package's per-day rate. The
    // installment rate itself is derived so the package total is fully
    // covered inside the chosen term (see CheckoutController::quote).
    'min_first_payment' => 10000,
    'min_first_days' => 10,

    // Domain options. The fee is a one-time payment, always due upfront
    // together with the first installment.
    'domains' => [
        'none' => ['label' => 'No domain — I already have one', 'fee' => 0],
        'subdomain' => ['label' => 'Free yourname.ttryy.com subdomain', 'fee' => 0],
        'budget' => ['label' => '.xyz / .online / .shop', 'fee' => 29000],
        'premium' => ['label' => '.com / .org', 'fee' => 80000],
    ],

    'frequencies' => ['full', 'monthly', 'weekly', 'daily'],

    // Hosting billing behaviour. Payment can be spread right down to a daily
    // rate across the plan term. A short grace period keeps a website online
    // after the renewal date slips, and reminder_days lists how many days
    // before the next due date the customer gets a nudge.
    'grace_days' => (int) env('HOSTING_GRACE_DAYS', 3),
    'reminder_days' => [7, 3, 1],

    // The daily/weekly/monthly figures below are legacy marketing reference
    // only. Installments are now derived from `price` across the term so the
    // total is always covered (CheckoutController::quote).
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
