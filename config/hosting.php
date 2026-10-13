<?php

return [
    // Public base domain used for Ttryy-managed subdomains
    // (e.g. business-name.ttryy.com). Never hardcode it elsewhere.
    'base_domain' => env('HOSTING_BASE_DOMAIN', 'ttryy.com'),

    // Reserved subdomain labels clients may never claim.
    'reserved_subdomains' => [
        'www', 'mail', 'admin', 'api', 'app', 'dashboard', 'cpanel',
        'ftp', 'blog', 'status', 'support', 'help', 'billing', 'secure',
        'staging', 'dev', 'test', 'ttryy', 'hosting', 'ns1', 'ns2',
    ],

    // Provisioning driver: 'manual' means websites are provisioned by the
    // Ttryy team and confirmed in the admin panel. Set to a real driver name
    // only when automated server provisioning is implemented and tested.
    'provisioning' => [
        'driver' => env('HOSTING_PROVISION_DRIVER', 'manual'),
    ],

    // Optional infrastructure integrations. Null = not configured; the UI
    // reports this honestly instead of simulating success.
    'dns' => [
        'driver' => env('HOSTING_DNS_DRIVER'),
    ],
    'ssl' => [
        'driver' => env('HOSTING_SSL_DRIVER'),
    ],

    // Service-to-service authentication (central <-> hosted websites).
    'service_auth' => [
        // Seconds a signed request stays valid (replay window).
        'tolerance' => (int) env('HOSTING_SERVICE_TOLERANCE', 300),
    ],

    // Central health checks of primary domains.
    'health' => [
        'timeout_seconds' => (int) env('HOSTING_HEALTH_TIMEOUT', 8),
    ],

    // Heartbeats: a service that has not reported within this many minutes
    // is shown as unreachable (never as suspended).
    'heartbeat_stale_minutes' => (int) env('HOSTING_HEARTBEAT_STALE_MINUTES', 60),

    // First-party analytics beacon. visitor_key = HMAC-SHA256(date|ip|ua).
    'analytics' => [
        'enabled' => env('HOSTING_ANALYTICS_ENABLED', true),
    ],
];
