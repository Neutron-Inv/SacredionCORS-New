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

        $partners = [
            'Tersus GNSS INC',
            'Association of Private Practicing Surveyors, Abia State (ABIA CORS)',
            'Association of Private Practicing Surveyors, Osun State (OSUN CORS)',
            'Nigerian Institution of Surveyors, Oyo State (OYO CORS)',
            'Prof. Ehigiator-Irughe, Raphael — Professor of Geodesy & Geomatics Engineering, University of Benin (GEOSYSTEM CORS)',
            'Association of Private Practicing Surveyors, Delta State (Delta CORS)',
            'Association of Private Practicing Surveyors, Ebonyi State (Abakaliki CORS)',
            'Department of Surveying and Geoinformatics, Federal University of Technology Minna (Gidan Kwano CORS)',
        ];

        $services = [
            'Cadastral Surveying',
            'Geographic Information Systems',
            'Instrument Set-up',
            'Hydrographic Surveys',
            'Aerial Survey',
        ];

        return view('home', compact('states', 'plans', 'partners', 'services'));
    }
}
