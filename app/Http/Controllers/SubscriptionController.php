<?php

namespace App\Http\Controllers;

use App\Facades\AssetPrice;
use App\Facades\Notification;
use Illuminate\View\View;
use App\Requests\CreatePriceSubscriptionRequest;
use App\Requests\CreatePercentageSubscriptionRequest;
use App\Interfaces\SubscriptionRepositoryInterface;
use Illuminate\Http\RedirectResponse;



class SubscriptionController extends Controller
{
    protected $subscriptionRepository;

    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function showSubscribeForm(): View
    {
        // AssetPrice::create();
        // Notification::processPercentageSubscriptions();
        // Notification::processPriceSubscriptions();
        return view('subscription', [
            'timeIntervals' => config('bitfinex.availableIntervals'),
            'symbols' => config('bitfinex.availableSymbols')
        ]);
    }

    public function priceSubscription(CreatePriceSubscriptionRequest $request): RedirectResponse
    {
        $exists = $this->subscriptionRepository->checkIfExists($request->validated());

        if (!$exists->count()) {
            $subs = $this->subscriptionRepository->create($request->validated());
            return redirect()->back()->with('message', 'Thanks for subscribing. We will notify you by email.');
        }

        return redirect()->back()->with('message', 'This email has already been subscribed for the entered price.');
    }

    public function percentSubscription(CreatePercentageSubscriptionRequest $request): RedirectResponse
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
