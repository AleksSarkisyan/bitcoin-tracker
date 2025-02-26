<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Requests\CreatePriceSubscriptionRequest;
use App\Requests\CreatePercentageSubscriptionRequest;
use App\Repositories\SubscriptionRepositoryInterface;

class SubscriptionController extends Controller
{
    protected $subscriptionRepository;

    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function showSubscribeForm(): View
    {
        return view('subscription', [
            'timeIntervals' => config('bitfinex.availableIntervals'),
            'symbols' => config('bitfinex.availableSymbols')
        ]);
    }

    public function priceSubscription(CreatePriceSubscriptionRequest $request)
    {
        $exists = $this->subscriptionRepository->checkIfExists($request->validated());

        if (!$exists->count()) {
            $subs = $this->subscriptionRepository->create($request->validated());
            return redirect()->back()->with('message', 'Thanks for subscribing. We will notify you by email.');
        }

        return redirect()->back()->with('message', 'This email has already been subscribed for the entered price.');
    }

    public function percentSubscription(CreatePercentageSubscriptionRequest $request)
    {
        $params = $request->validated();
        $exists = $this->subscriptionRepository->checkIfExists($params);

        if (!$exists->count()) {
            $this->subscriptionRepository->create($params);
            return redirect()->back()->with('messagePercentage', 'Thanks for subscribing for % change. We will notify you by email.');
        }

        return redirect()->back()->with('messagePercentage', 'You have already subscribed for this time period, percentage and symbol. Choose different combination.');
    }
}
