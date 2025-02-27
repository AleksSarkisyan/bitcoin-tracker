<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use App\Http\Controllers\SubscriptionController;
use App\Repositories\SubscriptionRepositoryInterface;
use App\Requests\CreatePriceSubscriptionRequest;
use App\Requests\CreatePercentageSubscriptionRequest;
use Illuminate\View\View;
use Mockery;
use Mockery\MockInterface;
use App\Models\Subscription;

class SubscriptionControllerTest extends TestCase
{
    protected MockInterface $subscriptionRepositoryMock;
    protected SubscriptionController $subscriptionController;
    protected $subscriptionControllerMock;
    protected $testEmail;

    protected function setUp(): void
    {
        parent::setUp();

        /**
         * @var \App\Repositories\SubscriptionRepositoryInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
         */
        $this->subscriptionRepositoryMock = Mockery::mock(SubscriptionRepositoryInterface::class);
        $this->subscriptionControllerMock = new SubscriptionController($this->subscriptionRepositoryMock);
        $this->testEmail = 'webi.aleks@gmail.com';
    }

    public function testShowSubscribeForm()
    {
        $view = $this->subscriptionControllerMock->showSubscribeForm();

        $this->assertInstanceOf(View::class, $view);
        $this->assertArrayHasKey('timeIntervals', $view->getData());
        $this->assertArrayHasKey('symbols', $view->getData());
    }

    public function testPriceSubscription()
    {
        $assetPrice = 80000;

        $requestMock = Mockery::mock(CreatePriceSubscriptionRequest::class);
        $requestMock->shouldReceive('validated')
            ->andReturn([
                'email' => $this->testEmail,
                'price' => $assetPrice
            ]
        );

        $this->subscriptionRepositoryMock->shouldReceive('checkIfExists')
            ->with(['email' => $this->testEmail, 'price' => $assetPrice])
            ->andReturn(collect([]));

        $this->subscriptionRepositoryMock->shouldReceive('create')
            ->with(['email' => $this->testEmail, 'price' => $assetPrice])
            ->andReturn(new(Subscription::class));

        $this->subscriptionControllerMock->priceSubscription($requestMock);

        $this->assertEquals('Thanks for subscribing. We will notify you by email.', session('message'));
    }

    public function testPercentSubscription()
    {
        $requestMock = Mockery::mock(CreatePercentageSubscriptionRequest::class);
        $requestMock->shouldReceive('validated')->andReturn(['email' => $this->testEmail, 'percent_change' => 5]);

        $this->subscriptionRepositoryMock->shouldReceive('checkIfExists')
            ->with(['email' => $this->testEmail, 'percent_change' => 5])
            ->andReturn(collect([]));

        $this->subscriptionRepositoryMock->shouldReceive('create')
            ->with(['email' => $this->testEmail, 'percent_change' => 5])
            ->andReturn(new(Subscription::class));

        $this->subscriptionControllerMock->percentSubscription($requestMock);

        $this->assertEquals('Thanks for subscribing for % change. We will notify you by email.', session('messagePercentage'));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
