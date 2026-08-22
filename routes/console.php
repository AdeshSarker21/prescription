<?php

use App\Console\Commands\SendReminders;
use Illuminate\Support\Facades\Schedule;

Schedule::command('reminders:send')->dailyAt('08:00');
