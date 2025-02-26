<?php

namespace App\Requests;
use Illuminate\Foundation\Http\FormRequest;

class CreatePercentageSubscriptionRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => 'required|email',
            'percent_change' => 'required|numeric|min:-100|max:100',
            'time_interval' => 'required|string',
            'symbol' => 'required|string'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Please enter a valid email!',
            'percent_change.numeric' => 'Please enter a valid number between 100 and -100!',
            'percent_change.min' => 'Number can\'t be smaller than -100.',
            'percent_change.max' => 'Number can\'t be greater than 100.'
        ];
    }
}
