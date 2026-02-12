<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $roles = ['Superadmin', 'Rendal', 'Admin Perwakilan', 'Korwas', 'Dalnis', 'Ketua Tim', 'Anggota'];

        foreach ($roles as $role) {
            Gate::define(\Illuminate\Support\Str::slug($role), function ($user) use ($role) {
                return $user->role->name === $role;
            });
        }
    }
}
