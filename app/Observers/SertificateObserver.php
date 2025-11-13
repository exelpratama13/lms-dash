<?php

namespace App\Observers;

use App\Models\Sertificate;
use App\Services\CertificateGeneratorService; // Added

class SertificateObserver
{
    protected $certificateGeneratorService; // Added

    public function __construct(CertificateGeneratorService $certificateGeneratorService) // Added
    {
        $this->certificateGeneratorService = $certificateGeneratorService; // Added
    }

    /**
     * Handle the Sertificate "created" event.
     */
    public function created(Sertificate $sertificate): void
    {
        $this->certificateGeneratorService->generatePdf($sertificate); // Added
    }

    /**
     * Handle the Sertificate "updated" event.
     */
    public function updated(Sertificate $sertificate): void
    {
        //
    }

    /**
     * Handle the Sertificate "deleted" event.
     */
    public function deleted(Sertificate $sertificate): void
    {
        //
    }

    /**
     * Handle the Sertificate "restored" event.
     */
    public function restored(Sertificate $sertificate): void
    {
        //
    }

    /**
     * Handle the Sertificate "force deleted" event.
     */
    public function forceDeleted(Sertificate $sertificate): void
    {
        //
    }
}
