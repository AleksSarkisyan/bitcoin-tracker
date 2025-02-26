<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class BitfinexService
{
    public $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function get($endpointType, $params = [])
    {
        if (!$endpointType || !isset($this->config[$endpointType])) {
            return $this->notFoundError();
        }

        $url = $this->buildApiUrl($params, $endpointType);

        $result = Http::log()->get($url);

        if (!$result->ok()) {
            return $this->responseError($result->status());
        }

        if ($this->config[$endpointType]['autoFormatResponse']) {
            return $this->formatResponse($endpointType, $result->json());
        }

        return $result->json();
    }

    public function post($path, $params = [])
    {
        /** HTTP post request logic */
    }

    public function notFoundError()
    {
        return [
            'code' => 404,
            'error' => config('errors.api.notFound')
        ];
    }

    public function responseError($statusCode)
    {
        return[
            'code' => $statusCode,
            'error' => config('errors.api.generic')
        ];
    }

    public function buildApiUrl($params, $endpointType)
    {
        $url = $this->config[$endpointType]['endpoint'];

        if (isset($params['query'])) {
            $url .= $params['query'];
        }

        return $url;
    }

    /** Adds field names instead of using Bitfinex indexes as keys */
    public function formatResponse($endpointType, $apiResponse)
    {
        $fields = config('bitfinex.v2.' . $endpointType . '.responseFields');

        foreach ($apiResponse as $key => $value) {
            $fields[$key] = Str::lower($fields[$key]);

            $apiResponse[$fields[$key]] = $apiResponse[$key];

            unset($apiResponse[$key]);
        }

        return $apiResponse;
    }
}
