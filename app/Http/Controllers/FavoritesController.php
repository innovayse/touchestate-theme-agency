<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('favorites');
    }

    public function load(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json(['html' => '', 'count' => 0, 'slugs' => []]);
    }
}
