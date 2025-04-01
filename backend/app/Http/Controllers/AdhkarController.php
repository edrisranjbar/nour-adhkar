<?php

namespace App\Http\Controllers;

use App\Models\Adhkar;
use App\Models\Collection;
use Illuminate\Http\Request;

class AdhkarController extends Controller
{
    public function index()
    {
        $adhkar = Adhkar::all();

        return response()->json([
            'success' => true,
            'adhkar' => $adhkar
        ]);
    }
} 