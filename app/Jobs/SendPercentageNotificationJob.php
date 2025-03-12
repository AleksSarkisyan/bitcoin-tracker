<?php

namespace App\Jobs;

use App\Mail\PercentageNotificationMail;
use App\Jobs\BaseMailNotificationJob;

class SendPercentageNotificationJob extends BaseMailNotificationJob
{
    public function getMailClass(): string
    {
        return PercentageNotificationMail::class;
    }

    public function getFieldToUpdate(): string
    {
        return 'percent_change_notified_on';
    }

    public function getJobName(): string
    {
        return $this->percentageNotificationJobName;
    }
}
