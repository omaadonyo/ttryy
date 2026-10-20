<?php

namespace App\Providers;

use App\Models\HostedWebsite;
use App\Models\WebsiteDomain;
use App\Policies\HostedWebsitePolicy;
use App\Policies\WebsiteDomainPolicy;
use App\Services\ActivityLogger;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
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

        Gate::policy(HostedWebsite::class, HostedWebsitePolicy::class);
        Gate::policy(WebsiteDomain::class, WebsiteDomainPolicy::class);

        // Capture the login IP address and record it in the activity trail.
        Event::listen(Login::class, function (Login $event) {
            $user = $event->user;
            $request = request();

            $user->forceFill([
                'last_login_ip' => $request?->ip(),
                'last_login_at' => now(),
                'last_login_agent' => $request ? substr((string) $request->userAgent(), 0, 255) : null,
            ])->save();

            app(ActivityLogger::class)->log($user->active_business_id, $user->id, 'login', 'Signed in');
        });

        View::composer(['layouts.app.sidebar', '*::app.sidebar'], function ($view) {
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
