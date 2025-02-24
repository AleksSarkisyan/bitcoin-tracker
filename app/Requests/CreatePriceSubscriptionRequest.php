<?php 

namespace App\Requests;
use Illuminate\Foundation\Http\FormRequest;

class CreatePriceSubscriptionRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => 'required|email',
            'target_price' => 'required|numeric|min:1'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Please enter a valid email!',
            'target_price.numeric' => 'Please enter a valid number!',
            'target_price.min' => 'Price must be greater than 0!'
        ];
    }
}