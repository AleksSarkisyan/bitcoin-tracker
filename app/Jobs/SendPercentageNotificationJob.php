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
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\DB;

class SendPercentageNotificationJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $historyPercentageChange;
    protected $tries = 3;

    public function __construct($user, $historyPercentageChange)
    {
        $this->user = $user;
        $this->historyPercentageChange = $historyPercentageChange;
    }

    public function middleware(): array
    {
        return [(new WithoutOverlapping($this->user->id . $this->user->email))->dontRelease()];
    }

    public function handle()
    {
        try {
            Mail::to($this->user['email'])->send(
                new PercentageNotificationMail($this->user,
                $this->historyPercentageChange)
            );

            Log::info('SendPercentageNotificationJob - Email sent successfully to: '. $this->user['email']);

            DB::transaction(function () {
                $this->user->update(['percent_change_notified_on' => Carbon::now()]);
            });
        } catch (\Exception $e) {
            Log::error('SendPercentageNotificationJob - Failed to send email to: ' . $this->user['email'] . ' - ' . $e->getMessage());
        }
    }

    public function failed(\Exception $exception)
    {
        Log::info('SendPercentageNotificationJob - Sending email failed: '. $this->user['email'] . ' - ' . $exception->getMessage());
        /** Additional logic to handle failed jobs */
    }
}
