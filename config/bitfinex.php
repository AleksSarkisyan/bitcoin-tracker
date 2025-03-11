<?php

define('BITFINEX_V2_API_BASEURL', env('BITFINEX_V2_API_BASEURL', 'https://api-pub.bitfinex.com/v2/'));

return [
    'v2' => [
        'ticker' => [
            'endpoint' => BITFINEX_V2_API_BASEURL . 'ticker/',
            'responseFields' => [
                'BID',
                'BID_SIZE',
                'ASK',
                'ASK_SIZE',
                'DAILY_CHANGE',
                'DAILY_CHANGE_RELATIVE',
                'LAST_PRICE',
                'VOLUME',
                'HIGH',
                'LOW'
            ],
            'autoFormatResponse' => true
        ],
        'tickers' => [
            'endpoint' => BITFINEX_V2_API_BASEURL . 'tickers'
        ],
        'candles' => [
            'endpoint' => BITFINEX_V2_API_BASEURL . 'candles/',
            'responseFields' => [
                'MTS',
                'OPEN',
                'CLOSE',
                'HIGH',
                'LOW',
                'VOLUME'
            ],
           'autoFormatResponse' => false
        ],
        'tickersHistory' => [
            'endpoint' => BITFINEX_V2_API_BASEURL . 'tickers/hist',
            'responseFields' => [
                'SYMBOL',
                'BID',
                null,
                'ASK',
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                null,
                'MTS'
            ],
           'autoFormatResponse' => false,
           'withAdditionalLogs' => false
        ]
    ],
    'v1' => [
        /** v1 configs go here */
    ],
    'availableSymbols' => ['tBTCUSD', 'tBTCEUR'],
    'availableIntervals' => ['1h', '6h', '24h']
];
