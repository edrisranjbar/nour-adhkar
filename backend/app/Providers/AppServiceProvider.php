<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\LeagueService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Notifications\ResetPasswordFa;

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
        // Avoid wrapping resource responses so tests expecting arrays pass
        JsonResource::withoutWrapping();

        // Use Persian RTL reset password email template
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            return (new ResetPasswordFa($token))->toMail($notifiable);
        });
    }
}
