<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Bitfinex extends Facade {
    
    /**
     * Get the registered name of the facade.
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'bitfinex';
    }
}