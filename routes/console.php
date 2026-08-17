<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('yamaha:sync')->weekly()->sundays()->at('02:00');

// Offers carry real expiry dates and "while stocks last" language, so they
// need fresher syncing than the model catalog (honda-catalog:sync, which
// isn't scheduled at all - see README, it's run manually).
Schedule::command('honda-catalog:sync-offers --with-assets')->daily()->at('03:00');
