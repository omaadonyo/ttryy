<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Foreign exchange
    |--------------------------------------------------------------------------
    |
    | Business Tools values are stored in a single base currency (the
    | business's own currency) and converted for display using live rates
    | quoted against USD. The endpoint below is a free, key-less provider;
    | swap it for any service that returns {"rates": {"UGX": ...}}.
    |
    */

    'endpoint' => env('FX_ENDPOINT', 'https://open.er-api.com/v6/latest/USD'),

    /*
    | Pin a deployment's own "standard" rates (1 USD = X). Any currency listed
    | here overrides the live value, which is handy for accounting teams that
    | must use a fixed corporate rate. Example: ['UGX' => 3800].
    */
    'overrides' => [],

    /*
    | Allow real HTTP calls while running the automated test suite. Off by
    | default so tests never depend on the network.
    */
    'allow_in_tests' => false,
];
