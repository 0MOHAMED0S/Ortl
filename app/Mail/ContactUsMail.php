<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactUsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    // Receive the form data
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->from($this->data['email'], $this->data['name']) // Sender's email
                    ->subject('رسالة جديدة من الموقع: ' . $this->data['subject']) // Email Subject
                    ->view('emails.contact_template'); // The view file (next step)
    }
}
