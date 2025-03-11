<?php

use App\Enums\SubscriptionTypes;

return [
    SubscriptionTypes::PRICE->value => [
        'success' => 'Thanks for subscribing. We will notify you by email.',
        'error' => 'This email has already been subscribed for the entered price.',
    ],
    SubscriptionTypes::PERCENTAGE->value => [
        'success' => 'Thanks for subscribing for % change. We will notify you by email.',
        'error' => 'You have already subscribed for this time period, percentage and symbol. Choose different combination.'
    ]
];
