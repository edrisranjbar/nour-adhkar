<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        JsonResource::withoutWrapping();

        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        ResetPassword::toMailUsing(function ($notifiable, $token) {
            return (new ResetPasswordFa($token))->toMail($notifiable);
        });
    }
}
