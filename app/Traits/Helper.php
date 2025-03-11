<?php

namespace App\Traits;

use App\Dtos\TickerHistoryParamsDto;
use Illuminate\Bus\Batch;
use Throwable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;


trait Helper {

    public static function batchJobs($jobs)
    {
        Bus::batch($jobs)->then(function (Batch $batch) {
            Log::info($batch->totalJobs .' jobs finished successfully.');
        })->catch(function (Batch $batch, Throwable $e) {
            Log::error('Job failed', ['error' => $e->getMessage()]);
            /** Some additional logic to handle failed jobs, e.g. retry and fallback mechanisms */
        })->allowFailures()->dispatch();
    }

    public static function buildHttpQuery(TickerHistoryParamsDto|array $params): array
    {
        $params = [
            'query' => '?' . http_build_query($params)
        ];

        return $params;
    }
}
