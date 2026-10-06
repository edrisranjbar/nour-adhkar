<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;

class InstagramController extends Controller
{
    public function __invoke()
    {
        $path = public_path('instagram/.vite/manifest.json');
        $manifest = is_file($path) ? json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR) : [];
        return view('panel.instagram', ['entry' => $manifest['src/panel-instagram.js'] ?? null]);
    }
}
