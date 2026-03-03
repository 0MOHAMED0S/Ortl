<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use Illuminate\Http\Request;

class ContactSettingController extends Controller
{
    public function index()
    {
        $settings = ContactSetting::first();

        if (!$settings) {
            return response()->json([
                'status' => false,
                'message' => 'Settings not found.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'email' => $settings->email,
                'phone' => $settings->phone,
            ]
        ]);
    }
}
