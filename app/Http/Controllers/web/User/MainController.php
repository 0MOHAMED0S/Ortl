<?php

namespace App\Http\Controllers\web\User;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use App\Models\Package;
use App\Models\Teacher;
use App\Models\Ad;
use App\Models\Track;

class MainController extends Controller
{
    public function index()
    {
        $packages = Package::where('status', 'active')->get();
        $contact = ContactSetting::first();
        $teachers = Teacher::with('user')
    ->whereHas('application', function($query) {
        $query->where('status', 'approved');
    })
    ->take(8)
    ->get();
        $ads = Ad::where('status', 'active')->get();
        $tracks = Track::where('status', 'active')->get();
        return view('welcome', compact('packages', 'contact', 'teachers', 'ads', 'tracks'));
    }
}
