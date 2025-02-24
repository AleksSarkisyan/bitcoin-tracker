<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\BitfinexService;

class BitfinexServiceProvider extends ServiceProvider
{
    /**
     * Register the Bitfinex service class.
     */
    public function register(): void
    {
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
