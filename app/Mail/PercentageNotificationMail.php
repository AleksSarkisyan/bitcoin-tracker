<?php

namespace App\Mail;

use App\Mail\BaseNotificationMail;

class PercentageNotificationMail extends BaseNotificationMail
{
    public function getEnvelopeSubject(): string
    {
        return 'Percentage Notification Mail';
    }

    public function getMailType(): string
    {
        return $this->percentageSubscriptionType;
    }
}
