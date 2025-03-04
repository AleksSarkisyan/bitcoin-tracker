<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use App\Services\NotificationService;
use App\Repositories\SubscriptionRepositoryInterface;
use App\Jobs\SendPercentageNotificationJob;
use App\Jobs\SendPriceNotificationJob;
use App\Facades\Bitfinex;
use Mockery;
use App\Models\Subscription;

class NotificationServiceTest extends TestCase
{
    public $subscriptionRepository;
    public $notificationService;

    public function setUp(): void
    {
        parent::setUp();

        /**
         * @var \App\Repositories\SubscriptionRepositoryInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
         */
        $this->subscriptionRepository = Mockery::mock(SubscriptionRepositoryInterface::class);
        $this->notificationService = new NotificationService($this->subscriptionRepository);
        $this->subscriber = $this->createFakeSubscriber();

        Config::set('bitfinex.availableSymbols', [$this->testSymbol]);
        Config::set('bitfinex.availableIntervals', ['1h', '6h', '24h']);
    }

    public function testProcessPercentageSubscriptions_batchesJobsWhenSubscribersExist()
    {
        $fakeUser = $this->createFakeUser();
        $percentageSubscribers = collect([
            $this->testSymbol => [
                '1h' => [$fakeUser],
                '6h' => [$fakeUser],
                '24h' => [$fakeUser]
            ]
        ]);

        $this->subscriptionRepository
            ->shouldReceive('getPercentageSubscribers')
            ->once()
            ->with(['1h', '6h', '24h'])
            ->andReturn($percentageSubscribers);

        Bitfinex::shouldReceive('get')
            ->once()
            ->with('ticker', ['query' => $this->testSymbol])
            ->andReturn(['bid' => 80000]);

        Bitfinex::shouldReceive('get')
            ->times(3)
            ->with('tickersHistory', Mockery::any())
            ->andReturn([$this->faketTickerHistoryResponse]);

        Bitfinex::shouldReceive('formatResponse')
            ->times(3)
            ->with('tickersHistory', $this->faketTickerHistoryResponse)
            ->andReturn(['bid' => 900]);

        Bus::fake();

        $result = $this->notificationService->processPercentageSubscriptions();

        $this->assertEquals('PercentageSubscriptionCommand - END!', $result);

        Bus::assertBatched(function ($batch) {
            return isset($batch->jobs) && $batch->jobs[0] instanceof SendPercentageNotificationJob;
        });
    }

    public function testCalculatePercentageDifference()
    {
        $diff = $this->notificationService->calculatePercentageDifference(900, 1000);
        $this->assertEquals(-10, $diff);
    }

    public function testGetSymbols()
    {
        $collection = collect([
            $this->testSymbol => ['1h' => []],
            'tBTCEUR' => ['1h' => []]
        ]);
        $symbols = $this->notificationService->getSymbols($collection);
        $this->assertEquals('tBTCUSD,tBTCEUR', $symbols);
    }

    public function testGetMsTimestamp()
    {
        $hours = 1;
        $timestampMs = $this->notificationService->getMsTimestamp($hours);
        $nowMs = Carbon::now()->timestamp * 1000;
        $oneHourAgoMs = Carbon::now()->subHour(1)->timestamp * 1000;

        $this->assertGreaterThanOrEqual($oneHourAgoMs, $timestampMs);
        $this->assertLessThanOrEqual($nowMs, $timestampMs);
    }

    public function testIsUserSubscribed()
    {
        // $user = new Subscription([$this->subscriber]);

        $user = new Subscription([
            'symbol' => $this->testSymbol,
            'time_interval' => '1h'
        ]);

        $hourPercentDiffs = [
            $this->testSymbol => ['1h' => -10]
        ];
        $this->assertTrue($this->notificationService->isUserSubscribed($hourPercentDiffs, $user));

        $user->time_interval = '2h';
        $this->assertFalse($this->notificationService->isUserSubscribed($hourPercentDiffs, $user));
    }

    public function testHasPriceDropped()
    {
        $this->assertTrue($this->notificationService->hasPriceDropped(-10, -11));
        $this->assertFalse($this->notificationService->hasPriceDropped(-10, -9));
    }

    public function testHasPriceJumped()
    {
        $this->assertTrue($this->notificationService->hasPriceJumped(10, 11));
        $this->assertFalse($this->notificationService->hasPriceJumped(10, 9));
    }

    public function testProcessPriceSubscriptionsFail()
    {
        Config::set('bitfinex.availableSymbols', [$this->testSymbol]);

        Bitfinex::shouldReceive('get')
            ->once()
            ->with('ticker', ['query' => $this->testSymbol])
            ->andReturn(['error' => 'some error']);

        $result = $this->notificationService->processPriceSubscriptions();
        $this->assertFalse($result);
    }

    public function testProcessPriceSubscriptions_dispatchesJobs()
    {
        Config::set('bitfinex.availableSymbols', [$this->testSymbol]);

        $tickerData = ['last_price' => '1000'];
        Bitfinex::shouldReceive('get')
            ->once()
            ->with('ticker', ['query' => $this->testSymbol])
            ->andReturn($tickerData);

        $this->subscriptionRepository->shouldReceive('getPriceSubscribers')
            ->once()
            ->with(1000, 'tBTCUSD')
            ->andReturn(Subscription::query());

        Bus::fake();

        $result = $this->notificationService->processPriceSubscriptions();

        $this->assertTrue($result);

        Bus::assertBatched(function ($batch) {
            return isset($batch->jobs) && $batch->jobs[0] instanceof SendPriceNotificationJob;
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
