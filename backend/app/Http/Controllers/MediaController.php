<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Get paginated media list
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Media::query()->orderBy('created_at', 'desc');
            
            // Filter by type
            if ($request->has('type')) {
                $type = $request->type;
                if ($type === 'image') {
                    $query->where('type', 'image/jpeg');
                } elseif ($type === 'audio') {
                    $query->where('type', 'like', 'audio/%');
                } elseif ($type === 'other') {
                    $query->where(function($q) {
                        $q->where('type', 'not like', 'image/%')
                          ->where('type', 'not like', 'audio/%');
                    });
                }
            }
            
            // Search by name or description
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhereJsonContains('tags', $search);
                });
            }
            
            // Paginate results
            $perPage = $request->input('limit', 12);
            $media = $query->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $media
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching media list: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت لیست رسانه‌ها'
            ], 500);
        }
    }
    
    /**
     * Get a single media item
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $media = Media::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'media' => $media
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching media item: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'رسانه مورد نظر یافت نشد'
            ], 404);
        }
    }
    
    /**
     * Upload new media files
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        try {
            // Log the request for debugging
            Log::info('Media upload request received');
            Log::info('Content-Type: ' . $request->header('Content-Type'));
            Log::info('Request has files: ' . ($request->hasFile('files') ? 'Yes' : 'No'));
            Log::info('Request has files[]: ' . ($request->hasFile('files[]') ? 'Yes' : 'No'));
            
            // Log all files
            $allFiles = $request->allFiles();
            Log::info('All files in request: ' . json_encode(array_keys($allFiles)));
            
            // Log raw request content
            Log::info('Request keys: ' . json_encode(array_keys($request->all())));
            
            // Try to iterate through all possible file inputs
            $hasAnyFiles = false;
            foreach ($request->all() as $key => $value) {
                if (is_array($value) && isset($value['tmp_name'])) {
                    $hasAnyFiles = true;
                    Log::info("Found file in key: {$key}");
                }
                
                if (str_contains($key, 'files')) {
                    Log::info("Found key with 'files': {$key}, value type: " . gettype($value));
                }
            }
            
            // Check for files with different possible field names
            $filesFieldName = null;
            $possibleFieldNames = ['files', 'files[]', 'file', 'media'];
            
            foreach ($possibleFieldNames as $fieldName) {
                if ($request->hasFile($fieldName)) {
                    $filesFieldName = $fieldName;
                    Log::info("Found files with field name: {$fieldName}");
                    break;
                }
            }
            
            // Check for indexed files
            for ($i = 0; $i < 10; $i++) {
                $indexedFieldName = "files[{$i}]";
                if ($request->hasFile($indexedFieldName)) {
                    $filesFieldName = $indexedFieldName;
                    Log::info("Found files with indexed field name: {$indexedFieldName}");
                    break;
                }
            }
            
            if (!$filesFieldName) {
                return response()->json([
                    'success' => false,
                    'message' => 'هیچ فایلی برای آپلود ارسال نشده است',
                    'debug' => [
                        'request_keys' => array_keys($request->all()),
                        'content_type' => $request->header('Content-Type'),
                        'has_any_files' => $hasAnyFiles,
                        'files' => $allFiles
                    ]
                ], 400);
            }
            
            $files = $request->file($filesFieldName);
            if (!is_array($files)) {
                $files = [$files];
            }
            
            Log::info('Number of files to process: ' . count($files));
            
            $validator = Validator::make($request->all(), [
                $filesFieldName . '.*' => 'required|file|max:20480', // 20MB max size
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            
            $uploadedMedia = [];
            
            foreach ($files as $file) {
                // Log file info
                Log::info('Processing file: ' . $file->getClientOriginalName() . ', size: ' . $file->getSize() . ', mime: ' . $file->getMimeType());
                
                // Generate a unique filename
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '_' . time() . '_' . Str::random(10) . '.' . $extension;
                
                // Store the file
                $path = $file->storeAs('uploads/media', $filename, 'public');
                
                // Create media record
                $media = new Media();
                $media->name = $originalName;
                $media->path = $path;
                $media->url = asset('storage/' . $path);
                $media->type = $file->getMimeType();
                $media->size = $file->getSize();
                $media->tags = [];
                $media->description = '';
                $media->save();
                
                $uploadedMedia[] = $media;
            }
            
            Log::info('Upload successful, media items created: ' . count($uploadedMedia));
            
            return response()->json([
                'success' => true,
                'message' => count($uploadedMedia) . ' فایل با موفقیت آپلود شد',
                'data' => [
                    'media' => $uploadedMedia
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading media: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'خطا در آپلود فایل‌ها: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update media item
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $media = Media::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|nullable|string',
                'tags' => 'sometimes|nullable|array',
                'tags.*' => 'string|max:50'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            
            // Update fields
            if ($request->has('name')) {
                $media->name = $request->name;
            }
            
            if ($request->has('description')) {
                $media->description = $request->description;
            }
            
            if ($request->has('tags')) {
                $media->tags = $request->tags;
            }
            
            $media->save();
            
            return response()->json([
                'success' => true,
                'message' => 'اطلاعات فایل با موفقیت بروزرسانی شد',
                'data' => [
                    'media' => $media
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating media: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'خطا در بروزرسانی اطلاعات فایل'
            ], 500);
        }
    }
    
    /**
     * Delete media item
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $media = Media::findOrFail($id);
            
            // Delete the file from storage
            if (Storage::disk('public')->exists($media->path)) {
                Storage::disk('public')->delete($media->path);
            }
            
            // Delete the record
            $media->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'فایل با موفقیت حذف شد'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting media: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف فایل'
            ], 500);
        }
    }
    
    /**
     * Delete multiple media items
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteMultiple(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:media,id'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }
            
            $ids = $request->ids;
            $mediaItems = Media::whereIn('id', $ids)->get();
            
            foreach ($mediaItems as $media) {
                // Delete the file from storage
                if (Storage::disk('public')->exists($media->path)) {
                    Storage::disk('public')->delete($media->path);
                }
                
                // Delete the record
                $media->delete();
            }
            
            return response()->json([
                'success' => true,
                'message' => count($ids) . ' فایل با موفقیت حذف شد'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting multiple media: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف فایل‌ها'
            ], 500);
        }
    }
} 