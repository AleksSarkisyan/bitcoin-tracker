<?php

namespace App\Enums;


enum EndpointNames: string {
    case TICKER = 'ticker';
    case HISTORY = 'tickersHistory';
}

