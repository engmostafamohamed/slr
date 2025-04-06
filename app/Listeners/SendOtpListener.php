<?php

namespace App\Listeners;

use App\Events\OtpGenerated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Helpers\OtpHelper;

class SendOtpListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OtpGenerated $event): void
    {
        OtpHelper::generateOtp($event->user, $event->type);
    }
}
