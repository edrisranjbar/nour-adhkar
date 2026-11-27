<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\LectureAiSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LectureAiSettingsController extends Controller
{
    public function edit(LectureAiSettings $settings)
    {
        $row = $settings->row();
        return response()->view('panel.lecture-ai-settings', ['settings' => $row,
            'models' => json_decode($row?->models ?? '[]', true)])->header('Cache-Control', 'no-store');
    }

    public function save(Request $request, LectureAiSettings $settings)
    {
        $validator = Validator::make($request->all(), ['base_url' => 'required|url:https|max:255',
            'api_key' => 'nullable|string|max:4096', 'transcription_model' => 'nullable|string|max:255',
            'summary_model' => 'nullable|string|max:255']);
        if ($validator->fails()) return back()->withErrors($validator); // Never flash secrets.
        $data = $validator->validated();
        $data['base_url'] = rtrim($data['base_url'], '/');
        $host = parse_url($data['base_url'], PHP_URL_HOST);
        if (parse_url($data['base_url'], PHP_URL_USER) || parse_url($data['base_url'], PHP_URL_QUERY)
            || !$host || $host === 'localhost' || filter_var($host, FILTER_VALIDATE_IP)) {
            return back()->withErrors(['base_url' => 'آدرس HTTPS عمومی سرویس را وارد کنید.']);
        }
        $old = $settings->row();
        foreach (gethostbynamel($host) ?: [] as $address) {
            if (!filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return back()->withErrors(['base_url' => 'آدرس سرویس باید عمومی باشد.']);
            }
        }
        $changed = $old && $old->base_url !== $data['base_url'];
        if ($changed && empty($data['api_key'])) return back()->withErrors(['api_key' => 'برای سرویس جدید کلید را وارد کنید.']);
        $models = $changed ? [] : json_decode($old?->models ?? '[]', true);
        if ($changed) $data['transcription_model'] = $data['summary_model'] = null;
        $available = array_column($models, 'id');
        foreach (['transcription_model', 'summary_model'] as $field) {
            if (!empty($data[$field]) && !in_array($data[$field], $available, true)) {
                return back()->withErrors([$field => 'مدل را از فهرست دریافت‌شده انتخاب کنید.']);
            }
        }
        if (empty($data['api_key'])) unset($data['api_key']);
        else $data['api_key'] = Crypt::encryptString($data['api_key']);
        if ($changed) $data['models'] = null;
        DB::table('lecture_ai_settings')->updateOrInsert(['id' => 1], $data + ['updated_at' => now()]);
        return back()->with('status', 'تنظیمات ذخیره شد.');
    }

    public function models(LectureAiSettings $settings)
    {
        try {
            if (!$settings->row()?->api_key) throw new \RuntimeException();
            DB::table('lecture_ai_settings')->where('id', 1)->update(['models' => json_encode($settings->fetch()), 'updated_at' => now()]);
            return back()->with('status', 'فهرست مدل‌ها دریافت شد. همهٔ مدل‌ها قابل انتخاب‌اند.');
        } catch (\Throwable) {
            return back()->withErrors(['models' => 'دریافت مدل‌ها ناموفق بود؛ آدرس سرویس و کلید را بررسی کنید.']);
        }
    }
}
