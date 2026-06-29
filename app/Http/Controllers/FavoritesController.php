<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function index()
    {
        return view('favorites');
    }

    public function load(Request $request)
    {
        return response()->json(['html' => '', 'count' => 0, 'slugs' => []]);
    }
}
