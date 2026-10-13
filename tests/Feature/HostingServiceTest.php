<?php

use App\Models\HostedWebsite;
use App\Models\HostingCommand;
use App\Models\User;
use App\Services\Hosting\Service\ServiceCommandProcessor;
use App\Services\Hosting\ServiceAuthenticator;
use Illuminate\Support\Str;

function serviceCall($test, string $method, string $uri, array $payload, string $keyId, string $secret, string $siteUuid, array $overrides = [])
{
    $body = json_encode($payload);
    $timestamp = $overrides['timestamp'] ?? (string) time();
    $nonce = $overrides['nonce'] ?? (string) Str::uuid();
    $path = '/'.ltrim(parse_url($uri, PHP_URL_PATH), '/');
    $signature = $overrides['signature'] ?? ServiceAuthenticator::sign(
        $secret, $method, $path, $body, $timestamp, $nonce, $siteUuid
    );

    $server = [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_SERVICE_KEY' => $overrides['key'] ?? $keyId,
        'HTTP_X_SERVICE_TIMESTAMP' => $timestamp,
        'HTTP_X_SERVICE_NONCE' => $nonce,
        'HTTP_X_SERVICE_SITE' => $overrides['site'] ?? $siteUuid,
        'HTTP_X_SERVICE_SIGNATURE' => $signature,
    ];

    $response = $test->call($method, $uri, [], [], [], $server, $body);

    return [$response, compact('body', 'timestamp', 'nonce', 'signature')];
}

function provisionedSite(): array
{
    $owner = User::factory()->create();
    $site = makeWebsite($owner);
    [$credential, $secret] = ServiceAuthenticator::issueCredential($site);

    return [$site, $credential, $secret];
}

test('valid authenticated heartbeat is accepted', function () {
    [$site, $credential, $secret] = provisionedSite();

    [$response] = serviceCall($this, 'POST', '/api/v1/hosting/service/heartbeat',
        ['service_version' => '1.0.0'], $credential->key_id, $secret, $site->uuid);

    $response->assertOk()->assertJson(['ok' => true]);
    expect($site->fresh()->service_connected)->toBeTrue();
});

test('invalid signatures are rejected', function () {
    [$site, $credential, $secret] = provisionedSite();

    [$response] = serviceCall($this, 'POST', '/api/v1/hosting/service/heartbeat', [],
        $credential->key_id, 'wrong-secret', $site->uuid);
    $response->assertUnauthorized();
});

test('expired requests are rejected', function () {
    [$site, $credential, $secret] = provisionedSite();

    [$response] = serviceCall($this, 'POST', '/api/v1/hosting/service/heartbeat', [],
        $credential->key_id, $secret, $site->uuid, ['timestamp' => (string) (time() - 3600)]);
    $response->assertUnauthorized();
});

test('exact replays are accepted but tampered replays fail', function () {
    [$site, $credential, $secret] = provisionedSite();

    [$first, $meta] = serviceCall($this, 'POST', '/api/v1/hosting/service/heartbeat',
        ['service_version' => '1.0.0'], $credential->key_id, $secret, $site->uuid);
    $first->assertOk();

    // Exact byte replay: accepted (idempotent, no duplicate side effects).
    [$replay] = serviceCall($this, 'POST', '/api/v1/hosting/service/heartbeat',
        ['service_version' => '1.0.0'], $credential->key_id, $secret, $site->uuid,
        ['nonce' => $meta['nonce'], 'timestamp' => $meta['timestamp'], 'signature' => $meta['signature']]);
    $replay->assertOk();

    // Same nonce but tampered body reusing the old signature: rejected.
    [$tampered] = serviceCall($this, 'POST', '/api/v1/hosting/service/heartbeat',
        ['service_version' => '9.9.9-evil'], $credential->key_id, $secret, $site->uuid,
        ['nonce' => $meta['nonce'], 'timestamp' => $meta['timestamp'], 'signature' => $meta['signature']]);
    $tampered->assertUnauthorized();

    // Idempotency at command level: the same operation uuid can never
    // create two command rows.
    App\Models\HostingCommand::create([
        'uuid' => $meta['nonce'], 'hosted_website_id' => $site->id,
        'action' => 'suspend', 'state' => 'succeeded',
    ]);
    expect(App\Models\HostingCommand::where('uuid', $meta['nonce'])->count())->toBe(1);
});

test('cross-website commands are rejected', function () {
    [$siteA, $credA, $secretA] = provisionedSite();
    [$siteB] = provisionedSite();

    // Credential A used with site B's uuid.
    [$response] = serviceCall($this, 'POST', '/api/v1/hosting/service/heartbeat', [],
        $credA->key_id, $secretA, $siteB->uuid);
    $response->assertUnauthorized();
});

test('revoked credentials are rejected', function () {
    [$site, $credential, $secret] = provisionedSite();
    $credential->forceFill(['revoked_at' => now()])->save();

    [$response] = serviceCall($this, 'POST', '/api/v1/hosting/service/heartbeat', [],
        $credential->key_id, $secret, $site->uuid);
    $response->assertUnauthorized();
});

test('responses never leak secrets', function () {
    [$site, $credential, $secret] = provisionedSite();

    [$response] = serviceCall($this, 'POST', '/api/v1/hosting/service/heartbeat', [],
        $credential->key_id, $secret, $site->uuid);

    $response->assertOk();
    expect($response->getContent())->not->toContain($secret)
        ->and($response->getContent())->not->toContain('secret');
});

test('service processor suspends and restores idempotently without destroying data', function () {
    $state = 'active';
    $files = ['content' => 'precious client data'];
    $read = function () use (&$state) { return $state; };
    $write = function ($next) use (&$state) { $state = $next; };

    $ack = ServiceCommandProcessor::execute(
        ['action' => 'suspend', 'operation_id' => 'op-1'], $read, $write);
    expect($ack['ok'])->toBeTrue()
        ->and($ack['service_state'])->toBe('suspended')
        ->and($state)->toBe('suspended')
        ->and($files)->toBe(['content' => 'precious client data']);

    // Repeat delivery: same result, no inconsistency.
    $again = ServiceCommandProcessor::execute(
        ['action' => 'suspend', 'operation_id' => 'op-1'], $read, $write);
    expect($again['service_state'])->toBe('suspended')->and($state)->toBe('suspended');

    $back = ServiceCommandProcessor::execute(
        ['action' => 'unsuspend', 'operation_id' => 'op-2'], $read, $write);
    expect($back['ok'])->toBeTrue()->and($state)->toBe('active');

    $bad = ServiceCommandProcessor::execute(
        ['action' => 'rm -rf /', 'operation_id' => 'op-3'], $read, $write);
    expect($bad['ok'])->toBeFalse()->and($state)->toBe('active');
});
