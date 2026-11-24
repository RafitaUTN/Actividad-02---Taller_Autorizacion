<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
        // Acceso exclusivo para administradores.
        Gate::define('access-admin-dashboard', function (User $user): bool {
            return $user->hasRole('administrador');
        });

        // Acceso exclusivo para profesores.
        Gate::define('access-professor-dashboard', function (User $user): bool {
            return $user->hasRole('profesor');
        });
    }
}
