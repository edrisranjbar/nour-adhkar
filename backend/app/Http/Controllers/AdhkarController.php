<?php

namespace App\Http\Controllers;

use App\Models\Adhkar;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function getFavorites()
    {
        try {
            $user = Auth::user();
            $favorites = $user->favorites ?? [];
            
            // Get the full adhkar objects for the favorite IDs
            $adhkar = Adhkar::whereIn('id', $favorites)->get();
            
            return response()->json([
                'success' => true,
                'favorites' => $adhkar
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching favorites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleFavorite($id)
    {
        try {
            $user = Auth::user();
            $favorites = $user->favorites ?? [];
            
            // Check if the adhkar exists
            $adhkar = Adhkar::find($id);
            if (!$adhkar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adhkar not found'
                ], 404);
            }
            
            $isFavorite = in_array($id, $favorites);
            
            if ($isFavorite) {
                // Remove from favorites
                $favorites = array_values(array_filter($favorites, fn($fav) => $fav != $id));
            } else {
                // Add to favorites
                $favorites[] = $id;
            }
            
            // Update user's favorites
            $user->favorites = $favorites;
            $user->save();
            
            return response()->json([
                'success' => true,
                'isFavorite' => !$isFavorite,
                'message' => !$isFavorite ? 'Added to favorites' : 'Removed from favorites'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error toggling favorite',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}