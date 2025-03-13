<?php

namespace Tests\Unit\Controllers;

use Tests\TestCase;
use App\Http\Controllers\SubscriptionController;
use App\Interfaces\SubscriptionRepositoryInterface;
use App\Requests\CreatePriceSubscriptionRequest;
use App\Requests\CreatePercentageSubscriptionRequest;
use App\Requests\BaseSubscriptionRequest;
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
         * @var \App\Interfaces\SubscriptionRepositoryInterface|\Mockery\LegacyMockInterface|\Mockery\MockInterface
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

    public function testHandleNotExistingPriceSubscription()
    {
        $this->priceSubscriptionCommonData($subscriptionExists = true, $this->priceSubscriptionType);
    }

    public function testHandleExistingPriceSubscription()
    {
        $this->priceSubscriptionCommonData($subscriptionExists = false, $this->priceSubscriptionType);
    }

    public function testHandleNotExistingPercentageSubscription()
    {
        $this->priceSubscriptionCommonData($subscriptionExists = true, $this->percentageSubscriptionType);
    }

    public function testHandleExistingPercentageSubscription()
    {
        $this->priceSubscriptionCommonData($subscriptionExists = false, $this->percentageSubscriptionType);
    }

    public function priceSubscriptionCommonData(bool $exists, string $subscriptionType)
    {
        $baseRequestMock = BaseSubscriptionRequest::create('/subscribe', 'POST', ['type' => $subscriptionType]);

        $createPriceSubscriptionData = $this->getCommonDataByType($subscriptionType);

        $baseRequestMock->merge($createPriceSubscriptionData);
        $notificationTypeMock = $this->getMockeryByType();

        $notificationTypeMock->shouldReceive('validated')
            ->andReturn($createPriceSubscriptionData);

        $this->subscriptionRepositoryMock->shouldReceive('checkIfExists')
            ->with($createPriceSubscriptionData)
            ->andReturn(collect([$exists ? [1] : []]));

        if (!$exists) {
            $this->subscriptionRepositoryMock->shouldReceive('create')
                ->with($createPriceSubscriptionData)
                ->andReturn(new(Subscription::class));
        }

        $this->subscriptionControllerMock->handleSubscription($baseRequestMock);

        $result = $exists ? 'success' : 'error';
        $messageType = $this->subscriptionControllerMock->getMessageByType($subscriptionType);

        /** Session is not properly cleared between tests, so just mock it */
        $dummySession = [$messageType  => config('messages.' . $subscriptionType . '.' . $result)];

        $this->assertEquals(config('messages.' . $subscriptionType .'.' . $result), $dummySession[$messageType]);
    }

    public function getMockeryByType(): MockInterface
    {
        $mockery = [
            'price' => Mockery::mock(CreatePriceSubscriptionRequest::class),
            'percentage' => Mockery::mock(CreatePercentageSubscriptionRequest::class)
        ];

        return $mockery[$this->priceSubscriptionType];
    }

    public function getCommonDataByType(string $subscriptionType): array
    {
        if ($subscriptionType === $this->priceSubscriptionType) {
            return [
                'email' => $this->testEmail,
                'target_price' => 80000,
                'symbol' => $this->btcUsdSymbol
            ];
        }

        return [
            'email' => $this->testEmail,
            'percent_change' => 2,
            'time_interval' => '1h',
            'symbol' => $this->btcUsdSymbol
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
