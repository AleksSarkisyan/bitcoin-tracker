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

class SendPriceNotificationJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $subscriber;
    protected $currentPrice;

    public function __construct($subscriber, $currentPrice)
    {
        $this->subscriber = $subscriber;
        $this->currentPrice = $currentPrice;
    }

    public function handle()
    {
        try {
            Mail::to($this->subscriber['email'])->send(
                new PriceNotificationMail($this->subscriber,
                $this->currentPrice)
            );

            Log::info('Email sent successfully to: '. $this->subscriber['email']);

            $this->subscriber->update(['target_price_notified_on' => Carbon::now()]);
        } catch (\Exception $e) {
            Log::error('Failed to send email to: ' . $this->subscriber['email'] . ' - ' . $e->getMessage());
        }
    }
}
