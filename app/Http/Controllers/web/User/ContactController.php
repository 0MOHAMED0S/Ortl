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
            // Option 1: Get email from Config (As per your code)
            $businessEmail = config('mail.from.address', 'info@wartil.com');

            // Option 2: Get email from Database (Better for dynamic settings)
            // $businessEmail = ContactSetting::value('email') ?? 'info@wartil.com';

            Mail::to($businessEmail)->send(
                new ContactUsMail($request->validated())
            );

            // AJAX RESPONSE: Success
            return response()->json([
                'status' => 'success',
                'message' => 'تم إرسال رسالتك بنجاح، سنتواصل معك قريباً!'
            ], 200);

        } catch (\Throwable $e) {

            Log::error('Contact email send error', [
                'data' => $request->validated(),
                'error' => $e->getMessage(),
            ]);

            // AJAX RESPONSE: Error
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء الإرسال، يرجى المحاولة لاحقاً.'
            ], 500);
        }
    }
}
