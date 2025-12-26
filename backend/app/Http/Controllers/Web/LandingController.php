<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    /**
     * Show the application landing page.
     */
    public function index()
    {
        $plans = \App\Models\Plan::with('features')
            ->where('status', 'active')
            ->orderBy('price')
            ->get();

        return view('welcome', compact('plans'));
    }
}
