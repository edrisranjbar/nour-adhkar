<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = Collection::all();

        return response()->json([
            'success' => true,
            'collections' => $collections
        ]);
    }
} 