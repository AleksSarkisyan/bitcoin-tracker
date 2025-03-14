<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Unit\Jobs\FakeSubscriber;
use App\Models\Subscription;
use App\Enums\SubscriptionTypes;
use App\Enums\SymbolNames;
use Mockery;

abstract class TestCase extends BaseTestCase
{
    public $fakeEmail = 'test@example.com';
    public $subscriber;
    public $fakeUser = [
        'id' => 1,
        'email' => 'test@example.com',
        'target_price' => 80000,
        'symbol' => 'tBTCUSD',
        'time_interval' => '1h',
        'id' => 123,
        'percent_change' => -10
    ];

    public $priceSubscriptionType = SubscriptionTypes::PRICE->value;
    public $percentageSubscriptionType = SubscriptionTypes::PERCENTAGE->value;
    public $btcUsdSymbol = SymbolNames::TBTCUSD->value;
    public $btcEurSymbol = SymbolNames::TBTCEUR->value;

    public $faketTickerHistoryResponse = [
        "tBTCUSD",
        84246,
        null,
        84260,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        null,
        1740758403000
    ];

    public $job;
    public $testSymbol = 'tBTCUSD';

    public function createFakeSubscriber()
    {
        return new FakeSubscriber($this->fakeUser);
    }

    public function createFakeUser()
    {
        return new Subscription($this->fakeUser);
    }

    public function getMockSubscriber()
    {
        $subscriber = Mockery::mock(Subscription::class);
        $subscriber->shouldIgnoreMissing();
        $subscriber->symbol = $this->btcUsdSymbol;
        $subscriber->time_interval = '1h';
        $subscriber->percent_change = 5;
        $subscriber->target_price = 80000;
        $subscriber->id = 123;
        $subscriber->email = $this->fakeEmail;

        return $subscriber;
    }
}
