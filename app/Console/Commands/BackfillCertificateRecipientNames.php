<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Sertificate;

class BackfillCertificateRecipientNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'certificates:backfill-recipient-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill recipient_name on existing sertificates from the related user name';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting backfill of recipient_name for sertificates...');

        $count = 0;

        Sertificate::whereNull('recipient_name')
            ->chunkById(100, function ($sertificates) use (&$count) {
                foreach ($sertificates as $certificate) {
                    $user = $certificate->user;
                    if ($user && $user->name) {
                        $certificate->recipient_name = $user->name;
                        $certificate->save();
                        $count++;
                    }
                }
            });

        $this->info("Backfill completed. Updated {$count} records.");

        return 0;
    }
}
