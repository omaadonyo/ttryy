<?php

use App\Models\BtDocument;
use App\Models\BtItem;
use App\Models\Business;
use App\Models\Suggestion;
use App\Models\User;
use App\Services\BusinessContext;

function planUser(bool $premium = false): User
{
    $user = User::factory()->create();
    $business = app(BusinessContext::class)->forUser($user);

    if ($premium) {
        $business->forceFill(['plan' => 'premium', 'premium_until' => now()->addMonth()])->save();
    }

    return $user;
}

function adminFor(): User
{
    return User::factory()->create(['is_admin' => true]);
}

function invoicePayload(): array
{
    return [
        'issue_date' => now()->toDateString(),
        'lines' => [['description' => 'Consulting', 'qty' => 1, 'unit_price' => 50000]],
    ];
}

test('the free plan caps a user at one business', function () {
    $user = planUser();

    $this->actingAs($user)->postJson('/dashboard/tools/businesses', ['name' => 'Second Ventures'])
        ->assertStatus(422);

    expect($user->businesses()->count())->toBe(1)
        ->and(Business::where('owner_id', $user->id)->count())->toBe(1);
});

test('premium lifts the business cap', function () {
    $user = planUser(true);

    $this->actingAs($user)->postJson('/dashboard/tools/businesses', ['name' => 'Second Ventures'])
        ->assertOk();

    expect($user->businesses()->count())->toBe(2);
});

test('the free plan caps the team at two members', function () {
    $user = planUser();

    $this->actingAs($user)->postJson('/dashboard/tools/staff', ['email' => 'one@example.com'])->assertOk();
    $this->actingAs($user)->postJson('/dashboard/tools/staff', ['email' => 'two@example.com'])->assertStatus(422);

    $business = Business::find($user->active_business_id);
    expect($business->users()->count())->toBe(2);
});

test('the free plan caps products at three', function () {
    $user = planUser();

    foreach (['A', 'B', 'C'] as $name) {
        $this->actingAs($user)->postJson('/dashboard/tools/items', [
            'name' => 'Product '.$name, 'cost_price' => 1000, 'sale_price' => 2000, 'stock' => 1,
        ])->assertOk();
    }

    $this->actingAs($user)->postJson('/dashboard/tools/items', [
        'name' => 'Product D', 'cost_price' => 1000, 'sale_price' => 2000, 'stock' => 1,
    ])->assertStatus(422);

    expect(BtItem::where('business_id', $user->active_business_id)->count())->toBe(3);
});

test('the free plan allows one document of each type', function () {
    $user = planUser();

    $this->actingAs($user)->postJson('/dashboard/tools/invoices', invoicePayload())->assertOk();
    $this->actingAs($user)->postJson('/dashboard/tools/invoices', invoicePayload())->assertStatus(422);

    // A different type is still available.
    $this->actingAs($user)->postJson('/dashboard/tools/quotations', invoicePayload())->assertOk();

    expect(BtDocument::where('business_id', $user->active_business_id)->where('type', BtDocument::INVOICE)->count())->toBe(1)
        ->and(BtDocument::where('business_id', $user->active_business_id)->where('type', BtDocument::QUOTATION)->count())->toBe(1);
});

test('premium unlocks unlimited documents', function () {
    $user = planUser(true);

    $this->actingAs($user)->postJson('/dashboard/tools/invoices', invoicePayload())->assertOk();
    $this->actingAs($user)->postJson('/dashboard/tools/invoices', invoicePayload())->assertOk();

    expect(BtDocument::where('business_id', $user->active_business_id)->where('type', BtDocument::INVOICE)->count())->toBe(2);
});

test('premium reports are masked with sample figures for free businesses', function () {
    $user = planUser();

    $this->actingAs($user)->getJson('/dashboard/tools/api/reports')
        ->assertOk()
        ->assertJsonPath('pnl.revenue', 4070000);

    $premium = planUser(true);

    $this->actingAs($premium)->getJson('/dashboard/tools/api/reports')
        ->assertOk()
        ->assertJsonPath('pnl.revenue', 0);
});

test('the activity log is masked for free businesses', function () {
    $user = planUser();

    $this->actingAs($user)->getJson('/dashboard/tools/api/activity')
        ->assertOk()
        ->assertJsonPath('activity.0.user', 'Sample User');
});

test('buying premium debits the wallet and unlocks the plan', function () {
    $user = planUser();
    $user->forceFill(['balance_ugx' => 10000])->save();

    $this->actingAs($user)->postJson('/dashboard/tools/subscribe', ['cycle' => 'monthly'])->assertOk();

    $business = Business::find($user->active_business_id);
    expect($user->fresh()->balance_ugx)->toBe(0)
        ->and($business->isPremium())->toBeTrue()
        ->and($business->subscriptions()->count())->toBe(1);

    // Real (empty) report data now comes through.
    $this->actingAs($user)->getJson('/dashboard/tools/api/reports')->assertJsonPath('pnl.revenue', 0);
});

test('premium cannot be bought without wallet funds', function () {
    $user = planUser();
    $user->forceFill(['balance_ugx' => 500])->save();

    $this->actingAs($user)->postJson('/dashboard/tools/subscribe', ['cycle' => 'monthly'])->assertStatus(422);

    expect(Business::find($user->active_business_id)->isPremium())->toBeFalse();
});

test('an admin can grant and revoke premium for a business', function () {
    $owner = planUser();
    $business = Business::find($owner->active_business_id);

    $this->actingAs(adminFor())->post("/admin/businesses/{$business->id}/premium", ['cycle' => 'monthly'])
        ->assertRedirect();

    expect($business->fresh()->isPremium())->toBeTrue();

    $this->actingAs(adminFor())->delete("/admin/businesses/{$business->id}/premium")->assertRedirect();

    expect($business->fresh()->isPremium())->toBeFalse();
});

test('users can suggest features, vote, and admins can ship them', function () {
    $user = planUser();

    $this->actingAs($user)->postJson('/dashboard/tools/suggestions', ['title' => 'Dark mode dashboard'])
        ->assertOk()
        ->assertJsonCount(1, 'suggestions');

    $suggestion = Suggestion::first();
    expect($suggestion->votes)->toBe(1);

    $voter = planUser();
    $this->actingAs($voter)->postJson("/dashboard/tools/suggestions/{$suggestion->id}/vote")->assertOk();
    expect($suggestion->fresh()->votes)->toBe(2);

    // Voting again removes the vote.
    $this->actingAs($voter)->postJson("/dashboard/tools/suggestions/{$suggestion->id}/vote")->assertOk();
    expect($suggestion->fresh()->votes)->toBe(1);

    $this->actingAs(adminFor())->patch("/admin/suggestions/{$suggestion->id}", ['status' => 'shipped'])
        ->assertRedirect();

    expect($suggestion->fresh()->status)->toBe('shipped');
});
