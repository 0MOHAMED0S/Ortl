<?php

namespace App\Http\Controllers\web\User;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use App\Models\Package;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function index()
{
    $packages = Package::where('status', 'active')->get();

    $contact = ContactSetting::first();

    return view('welcome', compact('packages', 'contact'));
}
}
