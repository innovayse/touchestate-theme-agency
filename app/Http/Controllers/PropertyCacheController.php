<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PropertyCacheController extends Controller
{
    public function forget(Request $request)
    {
        session()->save();
        $slug = $request->input('slug', '');
        if (is_string($slug) && strlen($slug) <= 200 && strlen($slug) > 0) {
            Cache::forget('te_prop:' . $slug);
        }
        return response()->json(['ok' => true]);
    }
}
