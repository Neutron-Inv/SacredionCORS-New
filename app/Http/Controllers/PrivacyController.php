<?php

namespace App\Http\Controllers;

class PrivacyController extends Controller
{
    public function __invoke()
    {
        return view('privacy', [
            'effectiveDate' => '25 September 2026',
            'lastUpdated' => '25 September 2026',
        ]);
    }
}
