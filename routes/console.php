<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:price-subscription-command')->everyMinute();
