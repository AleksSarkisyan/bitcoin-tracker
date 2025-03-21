<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionController;

Route::get('/', function () {
    return view('welcome');
});




Route::get('/subscribe', [SubscriptionController::class, 'showSubscribeForm']);
Route::post('/subscribe', [SubscriptionController::class, 'handleSubscription']);

