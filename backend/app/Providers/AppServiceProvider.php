<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\LeagueService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LeagueService::class, function ($app) {
            return new LeagueService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
