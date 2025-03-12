<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\NotificationService;
use App\Interfaces\SubscriptionRepositoryInterface;

class NotificationServiceProvider extends ServiceProvider
{

    /**
     * Register the NotificationService class.
     */
    public function register(): void
    {
        $this->app->singleton('notification', function ($app) {
            return new NotificationService(
                $app->make(SubscriptionRepositoryInterface::class)
            );
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
