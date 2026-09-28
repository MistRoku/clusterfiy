<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Nightly hygiene: drop stale unaccepted invites, downgrade companies
// whose payment grace window has expired. Runs via the system cron:
// * * * * * cd /var/www/clusterfiy && php artisan schedule:run >> /dev/null 2>&1
Schedule::command('invitations:purge-expired')->dailyAt('03:00');
Schedule::command('billing:enforce-grace')->dailyAt('03:15');
