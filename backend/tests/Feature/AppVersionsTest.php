<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('lists only published admin versions newest first with bounded pagination', function () {
    foreach (range(1, 22) as $code) {
        DB::table('app_versions')->insert([
            'version_code' => $code,
            'version_name' => '1.'.$code,
            'changelog' => "بهبود طراحی\nرفع اشکال",
            'release_date' => '2026-10-01',
            'published' => $code !== 22,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $this->getJson('/api/app-versions')->assertOk()
        ->assertJsonCount(20, 'data')
        ->assertJsonPath('data.0.versionCode', 21)
        ->assertJsonPath('data.0.changelog', "بهبود طراحی\nرفع اشکال")
        ->assertJsonPath('total', 21)
        ->assertJsonMissingPath('data.0.published')
        ->assertJsonMissingPath('data.0.created_at');

    $this->getJson('/api/app-versions?page=2')->assertOk()
        ->assertJsonCount(1, 'data')->assertJsonPath('data.0.versionCode', 1);

    DB::table('app_versions')->where('version_code', 21)->update(['published' => false]);
    DB::table('app_versions')->where('version_code', 20)->update(['changelog' => 'متن ویرایش‌شده در پنل']);
    $this->getJson('/api/app-versions')->assertOk()
        ->assertJsonPath('data.0.versionCode', 20)
        ->assertJsonPath('data.0.changelog', 'متن ویرایش‌شده در پنل');

    $this->getJson('/api/app-version')->assertOk()->assertJsonPath('versionCode', 20);
});

it('returns an empty history without published versions', function () {
    $this->getJson('/api/app-versions')->assertOk()->assertJsonCount(0, 'data');
});
