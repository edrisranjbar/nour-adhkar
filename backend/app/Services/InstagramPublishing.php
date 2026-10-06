<?php

namespace App\Services;

class InstagramPublishing
{
    public static function configured(): bool
    {
        return (bool) (config('services.instagram.enabled') && config('services.instagram.worker_token') && config('services.instagram.account_id'));
    }

    public static function renderToken(object $post): string
    {
        return hash('sha256', json_encode([$post->image_text, $post->translator_key, $post->design, $post->caption]));
    }
}
