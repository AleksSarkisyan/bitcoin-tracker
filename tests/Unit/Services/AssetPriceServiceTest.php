<?php

namespace Tests\Unit\Services;

use App\Services\AssetPriceService;
use App\Interfaces\SubscriptionRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;
use App\Interfaces\AssetPriceRepositoryInterface;
use App\Enums\ServiceNames;

class AssetPriceServiceTest extends TestCase
{
    protected $subscriptionRepoMock;
    protected $assetPriceRepoMock;
    protected $assetPriceService;
    protected $assetPriceServiceName = ServiceNames::ASSET_PRICE->value;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriptionRepoMock = Mockery::mock(SubscriptionRepositoryInterface::class);
        $this->assetPriceRepoMock = Mockery::mock(AssetPriceRepositoryInterface::class);

        $this->assetPriceService = new AssetPriceService(
            $this->subscriptionRepoMock,
            $this->assetPriceRepoMock
        );
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testCreateReturnsFalseWhenNoDataToInsert()
    {
        $emptyCollection = collect([]);
        $this->subscriptionRepoMock->shouldReceive('getUniqueSymbolHourPairs')
            ->once()
            ->andReturn($emptyCollection);

        Log::shouldReceive('info')
            ->once()
            ->with('AssetPriceService Got time/symbol pairs - ', []);

        $assetPriceDtoMock = Mockery::mock('alias:App\Dtos\AssetPriceDto');
        $assetPriceDtoMock->shouldReceive('optional')
            ->once()
            ->andReturn([]);

        Log::shouldReceive('info')
            ->once()
            ->with('AssetPriceService No data to insert');

        $result = $this->assetPriceService->create();

        $this->assertFalse($result);
    }
}
