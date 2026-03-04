<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\DonationProcessed;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class NotifyAdmins implements ShouldQueue
{
    public function handle(DonationProcessed $event): void
    {
        // Placeholder: In a real app, you might send a Slack notification or an email to admins.
        // For now, we'll just log it.
        Log::info("Donation processed: {$event->donation->id} for \${$event->donation->amount}");

        // Example:
        // Notification::send(User::role('admin')->get(), new NewDonationNotification($event->donation));
    }
}
