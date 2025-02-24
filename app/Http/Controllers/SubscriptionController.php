<?php
 
namespace App\Http\Controllers;
 
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Requests\CreatePriceSubscriptionRequest;
use App\Facades\Bitfinex;
use Illuminate\Support\Facades\Mail;
use App\Mail\PriceNotificationMail;
use App\Models\Subscription;
use App\Traits\SubscriptionTrait;
use App\Repositories\SubscriptionRepositoryInterface;
use Carbon\Carbon;
use App\Jobs\SendPriceNotificationJob;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
 
class SubscriptionController extends Controller
{
    use SubscriptionTrait;

    protected $subscriptionRepository;

    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    /**
     * Show the profile for a given user.
     */
    public function showSubscribeForm(): View
    {
        $availableSymbols = ['tBTCUSD', 'tBTCETH'];
        
        // Test functionlaity
        // $params = [
        //     'query' => 'tBTCUSD'
        // ];

        // $bitfinexApiData = Bitfinex::get($endpointType = 'ticker', $params);
        
        // if (!$bitfinexApiData || isset($bitfinexApiData['error'])) {
        //     return false;
        // }
        
        // $currentPrice = intval($bitfinexApiData['last_price']);
        
        // $subscribers = $this->subscriptionRepository()->getPriceSubscribers($currentPrice);

        // if (!$subscribers->count()) {
        //     Log::info('Nothing to process!');
            
        //     return view('subscription', [
        //         'symbols' => $availableSymbols
        //     ]);
        // }

        // $chunkSize = 100;

        // $subscribers->orderBy('id')
        //     ->chunkById($chunkSize, function ($subscribers) use ($bitfinexApiData) {

        //         $priceJobs = [];
        //         foreach ($subscribers as $subscriber) {
        //             $priceJobs[] = new SendPriceNotificationJob($subscriber, $bitfinexApiData['last_price']);
        //         }

        //         Bus::batch($priceJobs)->dispatch();

        //         return true;
        //     });

        return view('subscription', [
            'symbols' => $availableSymbols
        ]);
    }

    public function priceSubscription(CreatePriceSubscriptionRequest $request)
    {
        // with SubscriptionTrait
        // $result = $this->createPriceSubscription($request->validated());

        // if ($result) {
        //     return redirect()->back()->with('message', 'Thanks for subscribing. We will notify you by email.');
        // }

        // return redirect()->back()->with('message', 'This email has already been subscribed for the entered price.');

        $exists = $this->subscriptionRepository->checkIfExists($request->validated());
        
        if (!$exists->count()) {
            $subs = $this->subscriptionRepository->create($request->validated());
            return redirect()->back()->with('message', 'Thanks for subscribing. We will notify you by email.');
        }

        return redirect()->back()->with('message', 'This email has already been subscribed for the entered price.');
    }

    public function percentageSubscription(Request $request)
    {
        
    }
    
}