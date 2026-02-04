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

    public function privacy()
    {
        return view('web.privacy');
    }

    public function terms()
    {
        return view('web.terms');
    }

    public function services()
    {
        return view('web.services');
    }

    public function pricing()
    {
        $plans = \App\Models\Plan::with('features')
            ->where('status', 'active')
            ->orderBy('price')
            ->get();

        return view('web.pricing', compact('plans'));
    }

    public function contact()
    {
        return view('web.contact');
    }

    public function storeContact(\Illuminate\Http\Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'required|string',
        ]);

        \App\Models\Lead::create($validated);

        return back()->with('success', 'Thank you for contacting us! We will get back to you shortly.');
    }

    public function sitemap()
    {
        $urls = [
            route('home'),
            route('privacy'),
            route('terms'),
            route('services'),
            route('pricing'),
            route('contact'),
            // Add internal login page if we want it indexed? Probably no.
            // route('internal.login'),
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $priority = $url === route('home') ? '1.0' : '0.8';

            $xml .= '<url>';
            $xml .= '<loc>' . $url . '</loc>';
            $xml .= '<lastmod>' . now()->toAtomString() . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>' . $priority . '</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'text/xml'
        ]);
    }
}
