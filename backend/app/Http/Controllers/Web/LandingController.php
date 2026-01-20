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

    public function sitemap()
    {
        $urls = [
            route('home'),
            route('privacy'),
            route('terms'),
            // Add internal login page if we want it indexed? Probably no.
            // route('internal.login'),
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url>';
            $xml .= '<loc>' . $url . '</loc>';
            $xml .= '<lastmod>' . now()->toAtomString() . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'text/xml'
        ]);
    }
}
