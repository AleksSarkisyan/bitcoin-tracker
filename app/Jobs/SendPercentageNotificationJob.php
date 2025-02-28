<?php

namespace App\Jobs;

use App\Mail\PercentageNotificationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Batchable;

class SendPercentageNotificationJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $historyPercentageChange;

    public function __construct($user, $historyPercentageChange)
    {
        $this->user = $user;
        $this->historyPercentageChange = $historyPercentageChange;
    }

    public function handle()
    {
        try {
            Mail::to($this->user['email'])->send(
                new PercentageNotificationMail($this->user,
                $this->historyPercentageChange)
            );

            Log::info('SendPercentageNotificationJob - Email sent successfully to: '. $this->user['email']);

            $this->user->update(['percent_change_notified_on' => Carbon::now()]);
        } catch (\Exception $e) {
            Log::error('SendPercentageNotificationJob - Failed to send email to: ' . $this->user['email'] . ' - ' . $e->getMessage());
        }
    }
}
