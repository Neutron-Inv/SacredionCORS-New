<?php

namespace App\Http\Controllers;

use App\Models\CorsStation;
use App\Models\Plan;

class HomeController extends Controller
{
    public function index()
    {
        $states = CorsStation::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $plans = Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('home', compact('states', 'plans'));
    }
}
