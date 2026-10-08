<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

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
