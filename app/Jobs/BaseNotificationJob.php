<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Mail;
use App\Enums\SubscriptionTypes;
use App\Enums\Jobs;

abstract class BaseMailNotificationJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var \App\Models\Subscription $subscriber */
    public $subscriber;
    public $additionData;

    public $priceSubscriptionType = SubscriptionTypes::PRICE->value;
    public $percentageSubscriptionType = SubscriptionTypes::PERCENTAGE->value;
    public $priceNotificationJobName = Jobs::SEND_PRICE_NOTIFICATION->value;
    public $percentageNotificationJobName = Jobs::SEND_PERCENTAGE_NOTIFICATION->value;

    public $tries = 3;

    public function __construct($subscriber, $additionData)
    {
        $this->subscriber = $subscriber;
        $this->additionData = $additionData;
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->subscriber->id . $this->subscriber->email))];
    }

    abstract public function getMailClass(): string;

    abstract public function getFieldToUpdate(): string;

    abstract public function getJobName(): string;

    public function getSuccessLogMessage(): string
    {
        return $this->getJobName() . ' - Email sent successfully to: ' . $this->subscriber->email;
    }

    public function getFailureLogMessage(\Exception $e): string
    {
        return $this->getJobName() . ' - Failed to send email to: '. $this->subscriber->id . ' - ' . $this->subscriber->email . ' - ' . $e->getMessage();
    }

    public function handle()
    {
        try {
            $mailClass = $this->getMailClass();
            $mailInstance = new $mailClass($this->subscriber, $this->additionData);
            // Mail::to($this->subscriber->email)->send($mailInstance);

            Log::info($this->getSuccessLogMessage());

            $this->subscriber->update([$this->getFieldToUpdate() => Carbon::now()]);
        } catch (\Exception $e) {
            Log::error($this->getFailureLogMessage($e));
            throw $e;
        }
    }

    public function failed(\Exception $exception)
    {
        Log::info('BaseNotificationJob - Job failed for: ' . $this->subscriber->email . ' - ' . $exception->getMessage());
        // Additional failure handling logic if necessary.
    }
}
