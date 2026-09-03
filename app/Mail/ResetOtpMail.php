<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetOtpMail extends Mailable
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
            // Updated subject to be more specific to password reset
            subject: 'طلب إعادة تعيين كلمة المرور - ورتل',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp_reset',
            with: ['otp' => $this->otp],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
