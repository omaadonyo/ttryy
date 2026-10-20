<?php

use App\Models\HostedWebsite;
use App\Models\PackageOrder;
use App\Models\User;
use App\Models\WebsiteDomain;

test('placing an order auto-creates a pending website with the chosen subdomain', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/dashboard/checkout', [
        'package' => 'START',
        'billing_frequency' => 'monthly',
        'domain' => 'subdomain',
        'subdomain_label' => 'mybiz',
        'duration_months' => 3,
        'business_name' => 'Mybiz Ltd',
        'phone' => '+256 700 000001',
    ])->assertRedirect();

    $order = PackageOrder::first();
    $website = $order->website;

    expect($website)->not->toBeNull()
        ->and($website->status)->toBe(HostedWebsite::STATUS_PENDING)
        ->and($order->subdomain)->toBe('mybiz')
        ->and($website->primaryDomain->domain)->toBe('mybiz.ttryy.com')
        ->and($website->primaryDomain->type)->toBe(WebsiteDomain::TYPE_SUBDOMAIN);
});

test('placing an order captures a customers own domain as pending verification', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/dashboard/checkout', [
        'package' => 'GROW',
        'billing_frequency' => 'monthly',
        'domain' => 'none',
        'custom_domain' => 'Shop.MyBiz.com',
        'duration_months' => 6,
        'business_name' => 'Mybiz Ltd',
        'phone' => '+256 700 000002',
    ])->assertRedirect();

    $order = PackageOrder::first();
    $website = $order->website;

    expect($order->custom_domain)->toBe('shop.mybiz.com')
        ->and($website)->not->toBeNull()
        ->and($website->status)->toBe(HostedWebsite::STATUS_PENDING)
        ->and($website->primaryDomain->domain)->toBe('shop.mybiz.com')
        ->and($website->primaryDomain->type)->toBe(WebsiteDomain::TYPE_CUSTOM)
        ->and($website->primaryDomain->verification)->toBe('pending');
});

test('a purchased domain still gets a generated external website', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/dashboard/checkout', [
        'package' => 'START',
        'billing_frequency' => 'monthly',
        'domain' => 'budget',
        'duration_months' => 3,
        'business_name' => 'Alpha Studio',
        'phone' => '+256 700 000003',
    ])->assertRedirect();

    $website = PackageOrder::first()->website;

    expect($website)->not->toBeNull()
        ->and($website->status)->toBe(HostedWebsite::STATUS_PENDING)
        ->and($website->primaryDomain->type)->toBe(WebsiteDomain::TYPE_SUBDOMAIN)
        ->and($website->primaryDomain->verification)->toBe('verified');
});
