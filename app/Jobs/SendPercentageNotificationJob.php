<?php

namespace App\Jobs;

use App\Mail\PercentageNotificationMail;
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

class SendPercentageNotificationJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $subscriber;
    protected $historyPercentageChange;
    protected $tries = 3;

    public function __construct($subscriber, $historyPercentageChange)
    {
        $this->subscriber = $subscriber;
        $this->historyPercentageChange = $historyPercentageChange;
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->subscriber->id . $this->subscriber->email))->dontRelease()];
    }

    public function handle()
    {
        try {
            // Mail::to($this->subscriber->email)->send(
            //     new PercentageNotificationMail($this->subscriber,
            //     $this->historyPercentageChange)
            // );

            Log::info('SendPercentageNotificationJob - Email sent successfully to: '. $this->subscriber->email);

            $this->subscriber->update(['percent_change_notified_on' => Carbon::now()]);
        } catch (\Exception $e) {
            Log::error('SendPercentageNotificationJob - Failed to send email to: ' . $this->subscriber->email . ' - ' . $e->getMessage());
        }
    }

    public function failed(\Exception $exception)
    {
        Log::info('SendPercentageNotificationJob - Sending email failed: '. $this->subscriber->email . ' - ' . $exception->getMessage());
        /** Additional logic to handle failed jobs */
    }
}
