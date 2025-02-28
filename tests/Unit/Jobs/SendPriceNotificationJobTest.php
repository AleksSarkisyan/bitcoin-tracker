<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Jobs\SendPriceNotificationJob;
use App\Mail\PriceNotificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Tests\Unit\Jobs\FakeSubscriber;
use Mockery;

class SendPriceNotificationJobTest extends TestCase
{
    public $currentPrice;

    public function setUp(): void
    {
        parent::setUp();

        $this->currentPrice = 80000;
        $this->subscriber = $this->createFakeSubscriber();
        $this->job = new SendPriceNotificationJob($this->subscriber, $this->currentPrice);
    }

    public function testSendPriceNotificationJob()
    {
        Mail::fake();

        Log::shouldReceive('info')
            ->once()
            ->with('Email sent successfully to: ' . $this->fakeEmail);

        $subscriber = new FakeSubscriber(['email' => $this->fakeEmail]);

        $job = new SendPriceNotificationJob($subscriber, $this->currentPrice);
        $job->handle();

        Mail::assertSent(PriceNotificationMail::class, function ($mail) use ($subscriber) {
            return $mail->hasTo($subscriber['email'])
                && $mail->subscriber === $subscriber
                && $mail->currentPrice === $this->currentPrice;
        });

        $this->assertNotNull($subscriber->updatedData, 'Subscriber update was not called.');
        $this->assertArrayHasKey('target_price_notified_on', $subscriber->updatedData);
        $this->assertInstanceOf(Carbon::class, $subscriber->updatedData['target_price_notified_on']);
    }

    public function testMailExceptionError()
    {
        Mail::fake();
        Mail::shouldReceive('to')->andReturnSelf();
        Mail::shouldReceive('send')->andThrow(new \Exception('Test Exception'));

        Log::shouldReceive('error')
            ->once()
            ->with(\Mockery::on(function ($message) {
                return strpos($message, 'Failed to send email to: ' . $this->fakeEmail) !== false;
            }));

        $subscriber = new FakeSubscriber(['email' => $this->fakeEmail]);

        $job = new SendPriceNotificationJob($subscriber, $this->currentPrice);
        $job->handle();

        $this->assertNull($subscriber->updatedData, 'Should not update Subscriber.');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
