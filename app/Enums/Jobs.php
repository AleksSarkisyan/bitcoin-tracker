<?php

namespace App\Enums;

enum Jobs: string {
    case SEND_PRICE_NOTIFICATION = 'SendPriceNotificationJob';
    case SEND_PERCENTAGE_NOTIFICATION = 'SendPercentageNotificationJob';
}
