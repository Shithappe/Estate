<?php

namespace App\Providers;

use Inertia\Inertia;
use Illuminate\Support\ServiceProvider;

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
        Inertia::share('meta', function () {
            return [
                'og:title' => 'Estate Market',
                'og:description' => 'Estate market is a platform for real estate analysis and investment. We provide a wide range of tools for real estate analysis, investment, and management.',
                // 'og:image' => 'Default image URL',
                'og:url' => request()->url(),
            ];
        });
    }
}
