<?php

namespace App\Http\Controllers\web\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Contact\SendContactEmailRequest;
use App\Mail\ContactUsMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function sendEmail(SendContactEmailRequest $request)
    {
        try {
            $businessEmail = config('mail.from.address', 'info@wartil.com');

            Mail::to($businessEmail)->send(
                new ContactUsMail($request->validated())
            );

            return redirect()->back()
                ->with('success', 'تم إرسال رسالتك بنجاح، سنتواصل معك قريباً!');
        } catch (\Throwable $e) {

            Log::error('Contact email send error', [
                'data' => $request->validated(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'حدث خطأ أثناء الإرسال، يرجى المحاولة لاحقاً.');
        }
    }
}
