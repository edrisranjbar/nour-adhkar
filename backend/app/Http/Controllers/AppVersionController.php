<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class AppVersionController extends Controller
{
    /**
     * Latest published app version for the Android update check. Same keys as the old
     * version.json; minRequiredVersionCode is the newest published version marked required.
     */
    public function latest()
    {
        $latest = DB::table('app_versions')->where('published', true)->orderByDesc('version_code')->first();
        if (!$latest) {
            return response()->json(['message' => 'No published version'], 404);
        }
        $minRequired = (int) DB::table('app_versions')->where('published', true)->where('is_required', true)->max('version_code');

        return response()->json([
            'versionName' => $latest->version_name,
            'versionCode' => (int) $latest->version_code,
            'minRequiredVersionCode' => $minRequired,
            'changelog' => $latest->changelog,
            'releaseDate' => $latest->release_date,
        ]);
    }
}
