<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Sendotp extends Mailable
{
    use Queueable, SerializesModels;

    public $sentOtp;
    /**
     * Create a new message instance.
     */
    public function __construct($sentOtp)
    {
        $this->sentOtp = $sentOtp;
    }

    public function build()
    {
        return $this->subject('Verification Otp')
                    ->view('mail.sendotp');
    }
}
