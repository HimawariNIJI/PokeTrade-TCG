<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Refresh card market prices from pokemontcg.io daily.
Schedule::command('cards:refresh-prices')
    ->daily()
    ->withoutOverlapping()
    ->runInBackground();
