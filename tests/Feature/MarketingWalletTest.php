<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

test('wallet page shows balance and packs', function () {
    $this->actingAs(User::factory()->create())->get(route('wallet.index'))
        ->assertOk()
        ->assertSee('Top up', false);
});

test('token topup creates a pending order and verifies paid on callback', function () {
    config()->set('services.flutterwave.secret_key', 'test-secret');
    Http::fake([
        'https://api.flutterwave.com/*' => Http::response([
            'data' => ['id' => 555111, 'status' => 'successful', 'currency' => 'UGX', 'amount' => 20000],
        ], 200),
    ]);

    $user = User::factory()->create();

    $created = $this->actingAs($user)->postJson(route('wallet.topup'), ['pack' => 'growth'])
        ->assertOk()
        ->assertJsonPath('tokens', 500);

    $ref = $created->json('reference');

    $this->actingAs($user)->postJson(route('wallet.verify'), [
        'transaction_id' => '555111',
        'tx_ref' => $ref,
    ])->assertOk();

    expect($user->fresh()->token_balance)->toBe(500)
        ->and(App\Models\TokenTransaction::where('user_id', $user->id)->where('type', 'credit')->count())->toBe(1);
});

test('ai generation deducts tokens and falls back without a key', function () {
    config()->set('services.openai.key', null);
    $user = User::factory()->create();
    $user->forceFill(['token_balance' => 50])->save();

    $response = $this->actingAs($user)->postJson(route('marketing.generate'), [
        'business' => 'Nile Builders Ltd',
        'niche' => 'Construction',
        'tone' => 'professional',
        'goal' => 'first_outreach',
    ])->assertOk();

    expect($response->json('source'))->toBe('library')
        ->and($response->json('message'))->toContain('Nile Builders Ltd')
        ->and($user->fresh()->token_balance)->toBe(40);
});

test('ai generation is refused with insufficient balance', function () {
    $user = User::factory()->create();
    $user->forceFill(['token_balance' => 5])->save();

    $this->actingAs($user)->postJson(route('marketing.generate'), [])
        ->assertUnprocessable();

    expect($user->fresh()->token_balance)->toBe(5);
});

test('group unlock deducts once and is idempotent', function () {
    $user = User::factory()->create();
    $user->forceFill(['token_balance' => 50])->save();
    $group = App\Models\WhatsappGroup::create([
        'name' => 'Test Group', 'niche' => 'SMEs', 'description' => 'Test',
        'invite_link' => 'https://chat.whatsapp.com/TESTLINK',
        'member_count' => 100, 'token_cost' => 10, 'is_active' => true,
    ]);

    $first = $this->actingAs($user)->postJson(route('groups.unlock', $group))->assertOk();
    expect($user->fresh()->token_balance)->toBe(40);

    // Second unlock is free and does not double-charge.
    $this->actingAs($user)->postJson(route('groups.unlock', $group))
        ->assertOk()
        ->assertJsonPath('already', true);

    expect($user->fresh()->token_balance)->toBe(40);
    expect($first->json('invite_link'))->toBe('https://chat.whatsapp.com/TESTLINK');
});

test('group unlock is refused with insufficient balance', function () {
    $user = User::factory()->create();
    $group = App\Models\WhatsappGroup::create([
        'name' => 'Poor Group', 'description' => 'Test',
        'invite_link' => 'https://chat.whatsapp.com/POOR',
        'member_count' => 10, 'token_cost' => 10, 'is_active' => true,
    ]);

    $this->actingAs($user)->postJson(route('groups.unlock', $group))->assertUnprocessable();
});

test('users can save and delete their own message templates', function () {
    $user = User::factory()->create();

    $created = $this->actingAs($user)->postJson(route('marketing.templates.store'), [
        'name' => 'My closer',
        'body' => 'Hi {name}, ready to close?',
    ])->assertOk();

    expect(App\Models\MessageTemplate::where('user_id', $user->id)->count())->toBe(1);

    $this->actingAs($user)->deleteJson(route('marketing.templates.destroy', $created->json('id')))->assertOk();
    expect(App\Models\MessageTemplate::where('user_id', $user->id)->count())->toBe(0);
});

test('users cannot delete builtin or other users templates', function () {
    $user = User::factory()->create();
    $builtin = App\Models\MessageTemplate::create(['user_id' => null, 'name' => 'Built-in', 'body' => 'Hi']);

    $this->actingAs($user)->deleteJson(route('marketing.templates.destroy', $builtin))->assertForbidden();
});
