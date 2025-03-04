<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subscription;

class SubscriptionSeeder extends Seeder
{
    public function run()
    {
        $fakeRecordsCount = 1000;
        Subscription::factory()->count($fakeRecordsCount)->create();
    }
}
