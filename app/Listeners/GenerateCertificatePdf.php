<?php

namespace App\Listeners;

use App\Models\SertificateCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class GenerateCertificatePdf
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
    public function handle(SertificateCreated $event): void
    {
        //
    }
}
