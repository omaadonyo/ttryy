<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:make-admin {email}', function (string $email) {
    $user = User::where('email', $email)->first();

    if (! $user) {
        $this->error("No user found with email {$email}. They must register first.");
        return 1;
    }

    $user->forceFill(['is_admin' => true])->save();
    $this->info("{$email} is now an administrator.");

    return 0;
})->purpose('Grant administrator access to a registered user');

Artisan::command('app:test-mail {email}', function (string $email) {
    try {
        Mail::raw('Ttryy mail test — if you read this, sending works.', function ($message) use ($email) {
            $message->to($email)->subject('Ttryy mail test');
        });
    } catch (Throwable $e) {
        $this->error('Send failed: '.$e->getMessage());

        return 1;
    }

    $this->info("Test email handed to the mailer for {$email}.");

    return 0;
})->purpose('Send a test email to verify mail delivery');
