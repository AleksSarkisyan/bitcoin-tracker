<?php

namespace App\Jobs;

use App\Mail\PriceNotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;

class SendPriceNotificationJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $subscriber;
    protected $currentPrice;
    protected $tries = 3;

    public function __construct($subscriber, $currentPrice)
    {
        $this->subscriber = $subscriber;
        $this->currentPrice = $currentPrice;
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->subscriber->id . $this->subscriber->email))->dontRelease()];
    }

    public function handle()
    {
        try {
            Mail::to($this->subscriber['email'])->send(
                new PriceNotificationMail($this->subscriber,
                $this->currentPrice)
            );

            Log::info('SendPriceNotificationJob - Email sent successfully to: '. $this->subscriber['email']);

            DB::transaction(function () {
                $this->subscriber->update(['target_price_notified_on' => Carbon::now()]);
            });
        } catch (\Exception $e) {
            Log::error('SendPriceNotificationJob - Failed to send email to: ' . $this->subscriber['email'] . ' - ' . $e->getMessage());
        }
    }

    public function failed(\Exception $exception)
    {
        Log::info('SendPriceNotificationJob -Sending email failed: '. $this->subscriber['email'] . ' - ' . $exception->getMessage());
        /** Additional logic to handle failed jobs */
    }
}
