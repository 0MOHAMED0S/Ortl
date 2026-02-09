<?php

namespace App\Http\Controllers\web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Contact\UpdateContactSettingsRequest;
use App\Models\ContactSetting;
use Illuminate\Http\Request;

class ContactSettingController extends Controller
{
    public function updateContactSettings(UpdateContactSettingsRequest $request)
{
    // Use firstOrCreate to ensure a record exists
    $setting = ContactSetting::firstOrCreate([], [
        'email' => 'info@wartil.com',
        'phone' => '201110562097',
    ]);

    $setting->update($request->validated());

    return redirect()->back()->with('success', 'تم تحديث معلومات التواصل بنجاح');
}
}
