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
            ]
            ],
        'tickers' => [
            'endpoint' => BITFINEX_V2_API_BASEURL . 'tickers'
        ]
    ],
    'v1' => [
        /** v1 configs go here */
    ]
];