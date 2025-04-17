<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Adhkar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CollectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Collection::query();

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $collections = $query->with('adhkar')->get();

        return response()->json([
            'success' => true,
            'collections' => $collections
        ]);
    }

    /**
     * Get a collection by its slug
     */
    public function show($slug)
    {
        try {
            $collection = Collection::where('slug', $slug)
                ->with('adhkar')
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'collection' => $collection
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Collection not found'
            ], 404);
        }
    }

    // Admin methods for collection management
    
    public function adminIndex()
    {
        $collections = Collection::with('adhkar')->get();
        
        // Transform collections to match frontend expectations
        $collections->transform(function ($collection) {
            $collection->title = $collection->name;
            return $collection;
        });

        return response()->json([
            'success' => true,
            'collections' => $collections
        ]);
    }

    public function adminStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'adhkarIds' => 'nullable|array',
            'adhkarIds.*' => 'exists:adhkars,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $collection = Collection::create([
            'name' => $request->title,
            'description' => $request->description,
            'icon' => $request->icon ?? null,
        ]);

        // If adhkarIds are provided, update the associated adhkar
        if ($request->has('adhkarIds') && is_array($request->adhkarIds)) {
            foreach ($request->adhkarIds as $adhkarId) {
                $adhkar = Adhkar::find($adhkarId);
                if ($adhkar) {
                    $adhkar->collection_id = $collection->id;
                    $adhkar->save();
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Collection created successfully',
            'collection' => $collection->fresh('adhkar')
        ], 201);
    }

    public function adminShow($id)
    {
        $collection = Collection::with('adhkar')->findOrFail($id);
        
        // Transform the collection to match the frontend expectations
        $collection->title = $collection->name;

        return response()->json([
            'success' => true,
            'collection' => $collection
        ]);
    }

    public function adminUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'adhkarIds' => 'nullable|array',
            'adhkarIds.*' => 'exists:adhkars,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $collection = Collection::findOrFail($id);
        $collection->update([
            'name' => $request->title,
            'description' => $request->description,
            'icon' => $request->icon ?? $collection->icon,
        ]);

        // Handle adhkar associations
        if ($request->has('adhkarIds')) {
            // Clear previous associations by setting collection_id to null
            Adhkar::where('collection_id', $collection->id)
                  ->update(['collection_id' => null]);
            
            // Set new associations
            if (is_array($request->adhkarIds)) {
                foreach ($request->adhkarIds as $adhkarId) {
                    $adhkar = Adhkar::find($adhkarId);
                    if ($adhkar) {
                        $adhkar->collection_id = $collection->id;
                        $adhkar->save();
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Collection updated successfully',
            'collection' => $collection->fresh('adhkar')
        ]);
    }

    public function adminDestroy($id)
    {
        $collection = Collection::findOrFail($id);
        
        // Check if the collection has adhkar
        $adhkarCount = $collection->adhkar()->count();
        if ($adhkarCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete collection with adhkar. Remove adhkar first.'
            ], 422);
        }
        
        $collection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Collection deleted successfully'
        ]);
    }
} 