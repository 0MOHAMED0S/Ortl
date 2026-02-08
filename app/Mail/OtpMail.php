<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public $otp;

    public function __construct($otp)
    {
        $this->otp = $otp;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'رمز التحقق الخاص بك - ورتل',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp', // We will create this view next
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
