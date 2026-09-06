<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('journal:report morning')->timezone('Asia/Jakarta')->dailyAt('08:00');
Schedule::command('journal:report afternoon')->timezone('Asia/Jakarta')->dailyAt('13:00');
Schedule::command('journal:report night')->timezone('Asia/Jakarta')->dailyAt('19:00');
Schedule::command('journal:report missing')->timezone('Asia/Jakarta')->dailyAt('23:00');
