<?php

namespace App\Requests;

use App\Enums\SubscriptionTypes;
use Illuminate\Foundation\Http\FormRequest;

class BaseSubscriptionRequest extends FormRequest
{
    public $priceSubscriptionType = SubscriptionTypes::PRICE->value;
    public $percentageSubscriptionType = SubscriptionTypes::PERCENTAGE->value;

    public function rules()
    {
        return [
            'type' => ['required', "in:{$this->priceSubscriptionType},{$this->percentageSubscriptionType}"],
        ];
    }

    public function messages()
    {
        return [
            'type.required' => 'The subscription type is required.',
            'type.in' => "The subscription type must be either '{$this->priceSubscriptionType}' or '{$this->percentageSubscriptionType}'.",
        ];
    }
}
