<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Transaction;
use Illuminate\Support\Facades\Storage;

class TransactionReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public Transaction $transaction;
    public string $filePath;

    /**
     * Create a new message instance.
     */
    public function __construct(Transaction $transaction, string $filePath)
    {
        $this->transaction = $transaction;
        $this->filePath = $filePath;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = 'Receipt - ' . ($this->transaction->transaction_code ?? $this->transaction->booking_trx_id);

        $mail = $this->subject($subject)
            ->view('emails.receipt')
            ->with(['transaction' => $this->transaction]);

        // Attach file if exists
        if ($this->filePath && file_exists($this->filePath)) {
            $mail->attach($this->filePath, [
                'as' => basename($this->filePath),
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
