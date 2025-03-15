<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\SendPercentageNotificationJob;
use App\Mail\PercentageNotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Mockery;

// Needs refactoring to match current logic
class SendPercentageNotificationJobTest extends TestCase
{
    public $historyPercentageChange;

    public function setUp(): void
    {
        parent::setUp();

        $this->historyPercentageChange = 5;
        $this->subscriber = $this->createFakeSubscriber();
        $this->job = new SendPercentageNotificationJob($this->subscriber, $this->historyPercentageChange);
    }

    public function testSendPercentageNotificationJobSuccess()
    {
        Mail::fake();
        Log::shouldReceive('info')
            ->once()
            ->with('SendPercentageNotificationJob - Email sent successfully to: ' . $this->fakeEmail);

        $this->job->handle();

        Mail::assertSent(PercentageNotificationMail::class, function ($mail) {
            return $mail->hasTo($this->subscriber['email'])
                && $mail->user === $this->subscriber
                && $mail->historyPercentageChange === $this->historyPercentageChange;
        });

        $this->assertNotNull($this->subscriber->updatedData, 'Subscriber update was not called.');
        $this->assertArrayHasKey('percent_change_notified_on', $this->subscriber->updatedData);
        $this->assertInstanceOf(Carbon::class, $this->subscriber->updatedData['percent_change_notified_on']);

    }

    public function testSendPercentageNotificationFail()
    {
        Mail::fake();
        Mail::shouldReceive('to')->andReturnSelf();

        Log::shouldReceive('error')
            ->once()
            ->with(Mockery::on(function ($message) {
                return strpos($message, 'SendPercentageNotificationJob - Failed to send email to: '. $this->fakeEmail) !== false;
        }));

        $this->job->handle();

        $this->assertNull($this->subscriber->updatedData, 'Should not update Subscriber.');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
