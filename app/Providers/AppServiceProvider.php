<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        View::composer('layouts.app.sidebar', function ($view) {
            $user = auth()->user();
            if (! $user) {
                $view->with('navNotifications', collect())->with('navUnreadCount', 0);

                return;
            }
            $unread = $user->unreadNotifications()->latest()->take(6)->get();
            $read = $unread->count() < 8
                ? $user->readNotifications()->latest()->take(8 - $unread->count())->get()
                : collect();
            $view->with('navNotifications', $unread->concat($read));
            $view->with('navUnreadCount', $user->unreadNotifications()->count());
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
