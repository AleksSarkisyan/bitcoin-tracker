<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Unit\Jobs\FakeSubscriber;

abstract class TestCase extends BaseTestCase
{
    public $fakeEmail = 'test@example.com';
    public $subscriber;
    public $job;

    public function createFakeSubscriber()
    {
        return new FakeSubscriber(['email' => $this->fakeEmail]);
    }

}
