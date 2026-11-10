<?php

use App\Enums\ScrapingSpiderEnum;
use Illuminate\Support\Facades\Schedule;

Schedule::command('scraping:dispatch-companies --spider=' . ScrapingSpiderEnum::AirregiJobs->value)
    ->onOneServer()
    ->withoutOverlapping()
    ->dailyAt('06:30')
    ->timezone('Asia/Tokyo');

Schedule::command('scraping:dispatch-companies --spider=' . ScrapingSpiderEnum::IndeedJobDetails->value)
    ->onOneServer()
    ->withoutOverlapping()
    ->dailyAt('07:15')
    ->timezone('Asia/Tokyo');

Schedule::command('scraping:dispatch-companies --spider=' . ScrapingSpiderEnum::IndeedJobApplications->value)
    ->onOneServer()
    ->withoutOverlapping()
    ->dailyAt('07:30')
    ->timezone('Asia/Tokyo');
