<?php

namespace App\Jobs;

use App\Mail\PriceNotificationMail;
use App\Jobs\BaseMailNotificationJob;

class SendPriceNotificationJob extends BaseMailNotificationJob
{
    public function getMailClass(): string
    {
        return PriceNotificationMail::class;
    }

    public function getFieldToUpdate(): string
    {
        return 'target_price_notified_on';
    }

    public function getJobName(): string
    {
        return $this->priceNotificationJobName;
    }
}
