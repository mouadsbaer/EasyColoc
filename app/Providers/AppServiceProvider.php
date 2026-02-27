<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Collocation;
use App\Policies\CollocationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;
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
        Gate::policy(Collocation::class, CollocationPolicy::class);

        // Custom Blade directive for ultra-clean UI authoring
        Blade::if('owner', function (Collocation $collocation) {
            $membership = $collocation->memberships()->where('user_id', auth()->id())->first();
            return $membership && $membership->role === 'owner';
        });
    }
}
