<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeacherApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $salary;

    public function __construct(User $user, $password, $salary)
    {
        $this->user = $user;
        $this->password = $password;
        $this->salary = $salary;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'مبروك! تم قبول طلب انضمامك كمعلم في ورتل',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.teacher_approved',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
