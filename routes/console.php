<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:create-asset-price-command')->everyMinute();
Schedule::command('app:price-subscription-command')->everyMinute();
Schedule::command('app:percentage-subscription-command')->everyMinute();
