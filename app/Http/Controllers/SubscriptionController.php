<?php

namespace App\Http\Controllers;

use App\Enums\SubscriptionTypes;
use App\Facades\AssetPrice;
use App\Facades\Notification;
use Illuminate\View\View;
use App\Requests\CreatePriceSubscriptionRequest;
use App\Requests\CreatePercentageSubscriptionRequest;
use App\Interfaces\SubscriptionRepositoryInterface;
use App\Requests\BaseSubscriptionRequest;
use Illuminate\Http\RedirectResponse;


class SubscriptionController extends Controller
{
    protected $subscriptionRepository;
    public $priceSubscriptionType = SubscriptionTypes::PRICE->value;
    public $percentageSubscriptionType = SubscriptionTypes::PERCENTAGE->value;

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

    public function handleSubscription(BaseSubscriptionRequest $request): RedirectResponse
    {
        $type = $request->get('type');
        $rules = $this->getRules($type);
        $params = $request->validate($rules);

        $exists = $this->subscriptionRepository->checkIfExists($params);

        if (!$exists->count()) {
            $this->subscriptionRepository->create($params);
            return redirect()->back()->with($this->getMessageByType($type), config('messages.' . $type . '.success'));
        }

        return redirect()->back()->with($this->getMessageByType($type), config('messages.' . $type . '.error'));
    }

    public function getRules(string $type)
    {
        $rules = [
            $this->priceSubscriptionType => (new CreatePriceSubscriptionRequest())->rules(),
            $this->percentageSubscriptionType => (new CreatePercentageSubscriptionRequest())->rules()
        ];

        return $rules[$type];
    }

    public function getMessageByType(string $type)
    {
        $messageLabels = [
            $this->priceSubscriptionType => 'message',
            $this->percentageSubscriptionType => 'messagePercentage'
        ];

        return $messageLabels[$type];
    }
}
