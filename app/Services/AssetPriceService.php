<?php

namespace App\Services;

use App\Dtos\AssetPriceDto;
use App\Dtos\TickerHistoryParamsDto;
use App\Dtos\TickerResponseDto;
use App\Facades\Bitfinex;
use App\Models\AssetPrice;
use App\Facades\Notification;
use App\Interfaces\SubscriptionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use App\Traits\Helper;
use App\Enums\EndpointNames;
use App\Enums\ServiceNames;

class AssetPriceService extends ServiceProvider
{
    public $subscriptionRepository;
    public $assetPriceServiceName = ServiceNames::ASSET_PRICE->value;

    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function create(): bool
    {
        $uniquePairs = $this->subscriptionRepository->getUniqueSymbolHourPairs();
        $uniquePairsArray = $uniquePairs->toArray();

        Log::info($this->assetPriceServiceName . ' Got time/symbol pairs - ', $uniquePairsArray);

        $assetPrices = AssetPriceDto::optional();

        foreach ($uniquePairsArray as $key => $asset) {
            $timestampMs = Notification::getMsTimestamp(intval($asset['time_interval']));

            $tickerHistoryParams = new TickerHistoryParamsDto(
                ...[
                    'symbols' =>  $asset['symbol'],
                    'limit' => 1,
                    'end' => $timestampMs
                ]
            );

            $params = Helper::buildHttpQuery($tickerHistoryParams);
            $endpointType = EndpointNames::HISTORY->value;
            $tickerHistory = Bitfinex::get($endpointType, $params);

            $assetPrices[] =
                array_filter(
                    Bitfinex::formatResponse($endpointType, $tickerHistory[0]
                )
            );

            $tickerParams = [
                'query' => $asset['symbol']
            ];

            $tickerResponse = new TickerResponseDto(
                ...Bitfinex::get($endpointType = 'ticker', $tickerParams)
            );

            $assetPrices[$key]['current_price'] = $tickerResponse->bid;
            $assetPrices[$key]['time_interval'] = $asset['time_interval'];
            $assetPrices[$key]['percent_difference'] = Notification::calculatePercentageDifference($assetPrices[0]['bid'], $tickerResponse->bid);
            $assetPrices[$key]['created_at'] = Carbon::now();
            $assetPrices[$key]['updated_at'] = Carbon::now();
        }

        if (empty($assetPrices)) {
            Log::info($this->assetPriceServiceName . ' No data to insert');
            return false;
        }

        Log::info($this->assetPriceServiceName . ' Inserting data', $assetPrices);
        return AssetPrice::insert($assetPrices);
    }
}
