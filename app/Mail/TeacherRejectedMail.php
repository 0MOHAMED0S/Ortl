<?php

namespace App\Mail;

use App\Models\Teacher_application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeacherRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;

    /**
     * نمرر كائن الطلب (Application) لكي نستخدم بياناته في الإيميل
     */
    public function __construct(Teacher_application $application)
    {
        $this->application = $application;
    }

    /**
     * إعداد عنوان الرسالة (الموضوع)
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'تحديث بخصوص طلب انضمامك كمعلم في منصة ورتل',
        );
    }

    /**
     * ربط الملف بـ View الخاص بالرفض
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.teacher_rejected',
        );
    }

    /**
     * لا توجد مرفقات
     */
    public function attachments(): array
    {
        return [];
    }
}
