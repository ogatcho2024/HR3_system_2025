<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LandingController extends Controller
{
    /**
     * Show the landing page.
     */
    public function main()
    {
        // Get all services
        $services = DB::table('services')->get();

        // Get one featured service (if any)
        $featuredService = DB::table('services')
                            ->where('service_featured', 1)
                            ->first();

        // Return the view with data
        
        return view('main', compact('services', 'featuredService'));
    }
}
