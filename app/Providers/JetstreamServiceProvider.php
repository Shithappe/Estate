<?php

namespace App\Providers;

use App\Actions\Jetstream\DeleteUser;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Laravel\Jetstream\Jetstream;
use Inertia\Inertia;


class JetstreamServiceProvider extends ServiceProvider
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
        $this->configurePermissions();

        // Jetstream::deleteUsersUsing(DeleteUser::class);

        Inertia::share([
            'auth.user' => function (Request $request) {
                return $request->user()
                    ? $request->user()->only('id', 'name', 'email')
                    : null;
            },
        ]);
    }

    /**
     * Configure the permissions that are available within the application.
     */
    protected function configurePermissions(): void
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::permissions([
            'create',
            'read',
            'update',
            'delete',
        ]);
    }
}
