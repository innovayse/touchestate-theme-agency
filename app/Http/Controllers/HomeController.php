<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        $rentProperties      = [];
        $saleProperties      = [];
        $topViewedImages     = [];
        $typeCounts          = ['Apartment' => 0, 'House' => 0, 'Office' => 0, 'Villa' => 0];
        $typeImages          = ['Apartment' => [], 'House' => [], 'Office' => [], 'Villa' => []];
        $cityCounts          = [];
        $cityImages          = [];
        $stats               = ['totalProperties' => 0, 'totalAgents' => 0, 'totalCities' => 0];
        $faqSliderProperties = [];

        return view('index', compact(
            'rentProperties', 'saleProperties', 'stats',
            'typeCounts', 'cityCounts', 'topViewedImages', 'cityImages', 'typeImages',
            'faqSliderProperties'
        ));
    }
}
