<?php

namespace Tests\Unit\Services;

use App\Services\NotificationService;
use App\Interfaces\SubscriptionRepositoryInterface;
use App\Interfaces\AssetPriceRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Queue;

class NotificationServiceTest extends TestCase
{
    protected $subscriptionRepoMock;
    protected $assetPriceRepoMock;
    protected $notificationService;

    public function setUp(): void
    {
        parent::setUp();

        $this->subscriptionRepoMock = Mockery::mock(SubscriptionRepositoryInterface::class);
        $this->assetPriceRepoMock = Mockery::mock(AssetPriceRepositoryInterface::class);

        $this->notificationService = new NotificationService(
            $this->subscriptionRepoMock,
            $this->assetPriceRepoMock
        );
    }

    public function testProcessPercentageSubscriptionsNoPrices()
    {
        $this->assetPriceRepoMock->shouldReceive('getPriceBySymbolsAndHours')
            ->once()
            ->andReturn([]);

        $expectedMessage = 'SendPercentageNotificationJob No current prices found!';

        Log::shouldReceive('info')
            ->once()
            ->with($expectedMessage);

        $result = $this->notificationService->processPercentageSubscriptions();

        $this->assertEquals($expectedMessage, $result);
    }

    public function testProcessPercentageSubscriptionsWithSubscribers()
    {
        $currentPrices = [
            $this->btcUsdSymbol => [
                1 => [
                    ['percent_difference' => 10]
                ]
            ]
        ];
        $this->assetPriceRepoMock->shouldReceive('getPriceBySymbolsAndHours')
            ->once()
            ->andReturn($currentPrices);

        $subscriber = $this->getMockSubscriber();
        $builder = $this->chunkSubscribers($subscriber);
        $this->getSubscribers($builder, 'getPercentageSubscribers');

        Queue::fake();

        $result = $this->notificationService->processPercentageSubscriptions();

        $this->assertEquals('PercentageSubscriptionCommand - END!', $result);
    }

    public function testProcessPriceSubscriptionsNoPrices()
    {
        $this->assetPriceRepoMock->shouldReceive('getPriceBySymbols')
            ->once()
            ->andReturn(collect([]));

        $expectedMessage = 'SendPriceNotificationJob No current prices found!';

        Log::shouldReceive('info')
            ->once()
            ->with($expectedMessage);

        $result = $this->notificationService->processPriceSubscriptions();

        $this->assertEquals($expectedMessage, $result);
    }

    public function testProcessPriceSubscriptionsWithSubscribers()
    {
        $this->assetPriceRepoMock->shouldReceive('getPriceBySymbols')->andReturn(collect([
            (object)['symbol' => 'tBTCUSD', 'current_price' => 80000]
        ]));

        $subscriber = $this->getMockSubscriber();
        $builder = $this->chunkSubscribers($subscriber);
        $this->getSubscribers($builder, 'getPriceSubscribers');

        Queue::fake();

        $result = $this->notificationService->processPriceSubscriptions();

        $this->assertEquals('SendPriceNotificationJob - END!', $result);
    }

    public function testCalculatePercentageDifference()
    {
        $result = $this->notificationService->calculatePercentageDifference(150, 100);

        $this->assertEquals(50, $result);
    }

    public function testHasPriceDropped()
    {
        $result = $this->notificationService->hasPriceDropped(-8, -11);

        $this->assertTrue($result);
    }

    public function testHasPriceJumped()
    {
        $result = $this->notificationService->hasPriceJumped(5, 6);

        $this->assertTrue($result);
    }

    public function chunkSubscribers($subscriber)
    {
        $builder = Mockery::mock(Builder::class);
        $builder->shouldReceive('orderBy')
            ->with('id')
            ->andReturnSelf();

        $builder->shouldReceive('chunkById')
            ->with($this->notificationService->chunkSize, \Mockery::type('callable'))
            ->andReturnUsing(function ($chunkSize, $callback) use ($subscriber) {
                $callback(collect([$subscriber]));
                return $this;
            });

        return $builder;
    }

    public function getSubscribers($builder, $functionName)
    {
        $this->subscriptionRepoMock->shouldReceive($functionName)
            ->once()
            ->andReturn($builder);
    }

}
