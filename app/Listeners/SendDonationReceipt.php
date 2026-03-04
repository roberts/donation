<?php

namespace App\Listeners;

use App\Events\DonationProcessed;
use App\Mail\DonationReceipt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendDonationReceipt implements ShouldQueue
{
    public function handle(DonationProcessed $event): void
    {
        if ($event->donation->donor && $event->donation->donor->email) {
            Mail::to($event->donation->donor->email)->send(new DonationReceipt($event->donation));

            $event->donation->update(['receipt_sent_at' => now()]);
        }
    }
}
