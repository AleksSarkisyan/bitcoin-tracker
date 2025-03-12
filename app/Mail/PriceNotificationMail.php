<?php

namespace App\Mail;

use App\Mail\BaseNotificationMail;

class PriceNotificationMail extends BaseNotificationMail
{
    public function getEnvelopeSubject(): string
    {
        return 'Price Notification Mail';
    }

    public function getMailType(): string
    {
        return $this->priceSubscriptionType;
    }
}
