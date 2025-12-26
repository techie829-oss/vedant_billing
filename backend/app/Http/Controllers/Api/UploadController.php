<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    /**
     * Handle the incoming file upload.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:2048', // Max 2MB
        ]);

        if ($request->file('file')->isValid()) {
            $path = $request->file('file')->store('uploads', 'public');
            $url = Storage::url($path);

            return response()->json([
                'path' => $path,
                'url' => asset($url), // Return full URL for frontend
            ]);
        }

        return response()->json(['message' => 'File upload failed'], 500);
    }
}
