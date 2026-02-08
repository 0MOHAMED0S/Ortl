<?php

namespace App\Http\Controllers\web\User;

use App\Http\Controllers\Controller;
use App\Models\package;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function index()
    {
        $packages = package::where('status', 'active')->get();
        return view('welcome', compact('packages'));
    }
}
