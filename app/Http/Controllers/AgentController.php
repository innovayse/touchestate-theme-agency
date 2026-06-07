<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class AgentController extends Controller
{
    public function index()
    {
        $agents = [];
        return view('agent', compact('agents'));
    }

    public function show(string $slug)
    {
        abort(404);
    }
}
