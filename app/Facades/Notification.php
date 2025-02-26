<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Notification extends Facade {

    /**
     * Get the registered name of the facade.
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'notification';
    }
}
