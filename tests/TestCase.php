<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Unit\Jobs\FakeSubscriber;
use App\Models\Subscription;

abstract class TestCase extends BaseTestCase
{
    public $fakeEmail = 'test@example.com';
    public $subscriber;
    public $fakeUser = [
        'email' => 'test@example.com',
        'target_price' => 80000,
        'symbol' => 'tBTCUSD',
        'time_interval' => '1h',
        'id' => 123,
        'percent_change' => -10
    ];

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

}
