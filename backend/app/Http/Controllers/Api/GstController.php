<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GstState;
use Illuminate\Http\Request;

class GstController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function index(Request $request)
    {
        $states = GstState::orderBy('name')->get();
        return response()->json($states);
    }
}
