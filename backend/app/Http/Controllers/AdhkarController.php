<?php

namespace App\Http\Controllers;

use App\Models\Adhkar;
use App\Models\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

    public function show($slug)
    {
        $collection = Collection::where('slug', $slug)->first();
        $adhkar = Adhkar::where('collection_id', $collection->id)->get();
        return response()->json([
            'success' => true,
            'collection_name' => $collection->name,
            'dhikrs' => $adhkar
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
                'message' => 'خطا در دریافت لیست موارد مورد علاقه',
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
                    'message' => 'ذکر مورد نظر یافت نشد'
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
                'message' => !$isFavorite ? 'به لیست مورد علاقه اضافه شد' : 'از لیست مورد علاقه حذف شد',
                'dhikr' => $adhkar
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در تغییر وضعیت علاقه‌مندی',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Admin methods

    public function adminIndex()
    {
        $adhkar = Adhkar::with('collection')->get();

        // Transform collections to match frontend expectations
        $adhkar->transform(function ($dhikr) {
            if ($dhikr->collection) {
                $dhikr->collection->title = $dhikr->collection->name;
            }
            return $dhikr;
        });

        return response()->json([
            'success' => true,
            'adhkar' => $adhkar
        ]);
    }

    public function adminStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'prefix' => 'nullable|string',
            'arabic_text' => 'required|string',
            'translation' => 'required|string',
            'count' => 'required|integer|min:1',
            'collection_id' => 'required|exists:collections,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $adhkar = Adhkar::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'ذکر با موفقیت ایجاد شد',
            'adhkar' => $adhkar
        ], 201);
    }

    public function adminShow($id)
    {
        $adhkar = Adhkar::with('collection')->findOrFail($id);

        // Transform collection to match frontend expectations
        if ($adhkar->collection) {
            $adhkar->collection->title = $adhkar->collection->name;
        }

        return response()->json([
            'success' => true,
            'adhkar' => $adhkar
        ]);
    }

    public function adminUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'nullable|string|max:255',
            'prefix' => 'nullable|string',
            'arabic_text' => 'required|string',
            'translation' => 'required|string',
            'count' => 'required|integer|min:1',
            'collection_id' => 'required|exists:collections,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $adhkar = Adhkar::findOrFail($id);
        $adhkar->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'ذکر با موفقیت به‌روزرسانی شد',
            'adhkar' => $adhkar
        ]);
    }

    public function adminDestroy($id)
    {
        $adhkar = Adhkar::findOrFail($id);
        $adhkar->delete();

        return response()->json([
            'success' => true,
            'message' => 'ذکر با موفقیت حذف شد'
        ]);
    }
}