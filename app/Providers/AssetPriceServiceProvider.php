<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AssetPriceService;
use App\Interfaces\AssetPriceRepositoryInterface;
use App\Interfaces\SubscriptionRepositoryInterface;
use App\Services\BitfinexService;
use App\Services\NotificationService;


class AssetPriceServiceProvider extends ServiceProvider
{
    /**
     * Register the AssetPriceService class.
     */
    public function register(): void
    {
        $this->app->singleton('assetprice', function($app) {
            return new AssetPriceService($app->make(SubscriptionRepositoryInterface::class));
        });

        $this->app->singleton('notification', function ($app) {
            return new NotificationService(
                $app->make(SubscriptionRepositoryInterface::class),
                $app->make(AssetPriceRepositoryInterface::class)
            );
        });

        $this->app->singleton('bitfinex', function() {
            $config = config('bitfinex.v2');
            return new BitfinexService($config);
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
