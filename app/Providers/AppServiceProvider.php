<?php

namespace App\Providers;

use App\Enums\UserRole;
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
        // Superadmin boleh melakukan apapun (default allow).
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperadmin()) {
                return true;
            }

            return null;
        });

        // Akses dashboard per role.
        foreach (UserRole::cases() as $role) {
            Gate::define("access-dashboard-{$role->value}", fn (User $user) => $user->hasRole($role));
        }

        // Area admin: superadmin (via before) + admin akademik.
        Gate::define('access-admin-area', fn (User $user) => $user->hasRole(UserRole::AdminAcademic));

        // Area pengajar: dosen (dan admin via kebutuhan operasional dibatasi policy modul).
        Gate::define('access-lecturer-area', fn (User $user) => $user->hasRole(UserRole::Lecturer));

        // Area mahasiswa.
        Gate::define('access-student-area', fn (User $user) => $user->hasRole(UserRole::Student));
    }
}

