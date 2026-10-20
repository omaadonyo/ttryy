<?php

/*
 * ttryy hosted-website service kit — configuration.
 *
 * Copy this file into the hosted site's config/ directory and fill the
 * key id + secret from the ttryy dashboard (Hosting → your website →
 * Service connection → Issue credential). Never commit the secret.
 */

return [
    // Credentials issued by the central ttryy platform.
    'key_id' => env('TTRYY_SERVICE_KEY_ID', env('HOSTING_SERVICE_KEY_ID')),
    'secret' => env('TTRYY_SERVICE_SECRET', env('HOSTING_SERVICE_SECRET')),
    'site_uuid' => env('TTRYY_SERVICE_SITE', env('HOSTING_SERVICE_SITE')),

    // Reject signed requests older than this many seconds (replay window).
    'tolerance' => (int) env('TTRYY_SERVICE_TOLERANCE', 300),

    // Where the local service state is persisted on this site.
    'state_path' => env('TTRYY_SERVICE_STATE', storage_path('app/ttryy-service.json')),

    // 'middleware' restricts visitors with HTTP 503 while suspended;
    // 'flag' records state only and lets your web server decide.
    'mode' => env('TTRYY_SERVICE_MODE', 'middleware'),

    // Visitor-facing notice shown while the site is suspended.
    'notice' => [
        'title' => env('TTRYY_SERVICE_NOTICE_TITLE', 'This website is temporarily unavailable'),
        'body' => env(
            'TTRYY_SERVICE_NOTICE_BODY',
            'This site is paused while its subscription is being settled. No content has been deleted and everything returns the moment the balance is cleared.'
        ),
        'contact' => env('TTRYY_SERVICE_CONTACT'),
    ],
];
