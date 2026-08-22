<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\Appointment;
use App\Notifications\AppointmentReminder;
use App\Services\SubscriptionExpiryService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendReminders extends Command
{
    protected $signature = 'reminders:send';
    protected $description = 'Send subscription and appointment reminders to doctors';

    public function handle(): void
    {
        $this->expireOverdueSubscriptions();
        $this->sendSubscriptionReminders();
        $this->sendAppointmentReminders();
    }

    protected function expireOverdueSubscriptions(): void
    {
        $expiryService = app(SubscriptionExpiryService::class);
        $expired = $expiryService->expireOverdueSubscriptions();

        if ($expired > 0) {
            $this->info("Expired {$expired} overdue subscriptions.");
        }
    }

    protected function sendSubscriptionReminders(): void
    {
        $expiryService = app(SubscriptionExpiryService::class);
        $sent = $expiryService->sendExpiryReminders();

        if ($sent > 0) {
            $this->info("Sent {$sent} subscription expiry reminders.");
        }
    }

    protected function sendAppointmentReminders(): void
    {
        Appointment::with(['doctor', 'patient'])
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->where('appointment_date', '>=', Carbon::now())
            ->chunk(100, function ($appointments) {
                foreach ($appointments as $appt) {
                    $diffDays = (int) Carbon::now()->startOfDay()->diffInDays(
                        $appt->appointment_date->startOfDay(), false
                    );

                    if ($diffDays === 0) {
                        $appt->doctor->notify(new AppointmentReminder($appt, 'today'));
                    } elseif ($diffDays === 1) {
                        $appt->doctor->notify(new AppointmentReminder($appt, 'upcoming'));
                    }
                }
            });
    }
}
