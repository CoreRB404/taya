<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;
use App\Policies\AdminPolicy;
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
        Password::defaults(fn () => Password::min(12)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols());

        Gate::define('manage-users', [AdminPolicy::class, 'manageUsers']);
        Gate::define('manage-facilities', [AdminPolicy::class, 'manageFacilities']);
        Gate::define('manage-penalties', [AdminPolicy::class, 'managePenalties']);
        Gate::define('view-audit-logs', [AdminPolicy::class, 'viewAuditLogs']);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
