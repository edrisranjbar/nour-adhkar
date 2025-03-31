<?php

namespace App\Http\Controllers;

use App\Models\DefaultDhikr;
use Illuminate\Http\Request;

class DefaultDhikrController extends Controller
{
    public function index()
    {
        $dhikrs = DefaultDhikr::all();
        return response()->json($dhikrs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'count' => 'required|integer|min:1',
            'prefix' => 'nullable|string',
            'suffix' => 'nullable|string',
            'translation' => 'required|string'
        ]);

        $dhikr = DefaultDhikr::create($validated);
        return response()->json($dhikr, 201);
    }
} 