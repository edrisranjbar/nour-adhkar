<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class LectureAiSettings
{
    public function row()
    {
        return DB::table('lecture_ai_settings')->where('id', 1)->first();
    }

    public function key(): string
    {
        return $this->row()?->api_key ? Crypt::decryptString($this->row()->api_key) : '';
    }

    public static function free(array $model): bool
    {
        $pricing = $model['pricing'] ?? [];
        foreach ($pricing as $price) {
            if (is_numeric($price) && (float)$price !== 0.0) return false;
        }
        return isset($pricing['prompt'], $pricing['completion'])
            && is_numeric($pricing['prompt']) && is_numeric($pricing['completion'])
            && (float)$pricing['prompt'] === 0.0 && (float)$pricing['completion'] === 0.0;
    }

    public function fetch(): array
    {
        $response = Http::withToken($this->key())->acceptJson()->timeout(20)->withoutRedirecting()
            ->get(rtrim($this->row()->base_url, '/').'/models');
        if (!$response->successful() || !is_array($response->json('data'))) {
            throw new \RuntimeException('Could not fetch models');
        }
        $models = [];
        foreach ($response->json('data') as $model) {
            if (is_array($model) && is_string($model['id'] ?? null) && strlen($model['id']) <= 255) {
                $models[] = ['id' => $model['id'], 'free' => self::free($model)];
            }
        }
        return $models;
    }

    public function worker(): ?array
    {
        $row = $this->row();
        if (!$row) return null; // Preserve the existing Groq worker until settings are created.
        if (!$row->transcription_model || !$row->summary_model || !$row->api_key) {
            throw new \RuntimeException('Configure lecture AI in admin settings');
        }
        $free = array_column(array_filter(json_decode($row->models ?? '[]', true), fn ($m) => $m['free']), 'id');
        if (!in_array($row->transcription_model, $free, true) || !in_array($row->summary_model, $free, true)) {
            throw new \RuntimeException('Select confirmed free models');
        }
        return ['base_url' => $row->base_url, 'api_key' => $this->key(),
            'transcription_model' => $row->transcription_model, 'summary_model' => $row->summary_model];
    }
}
