<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('accepts typed feedback and only exposes published notices', function () {
    $this->postJson('/api/app-feedback', ['type' => 'invalid', 'message' => 'Hello'])->assertUnprocessable();
    $this->postJson('/api/app-feedback', ['type' => 'suggestion', 'message' => 'A helpful suggestion'])->assertCreated();
    expect(DB::table('app_feedback')->count())->toBe(1);

    $published = DB::table('app_notices')->insertGetId(['title' => 'Public', 'message' => 'Hello', 'published' => true, 'created_at' => now(), 'updated_at' => now()]);
    DB::table('app_notices')->insert(['title' => 'Draft', 'message' => 'Hidden', 'published' => false, 'created_at' => now(), 'updated_at' => now()]);
    $installationId = 'dcfd33bc-9f0f-4cde-ae94-af0f94f128f9';
    $this->getJson('/api/app-notices?installation_id='.$installationId)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Public')->assertJsonPath('data.0.read_at', null);
    $this->postJson("/api/app-notices/$published/read", ['installation_id' => $installationId])->assertOk();
    $this->postJson("/api/app-notices/$published/read", ['installation_id' => $installationId])->assertOk();
    expect(DB::table('app_notice_reads')->count())->toBe(1);
    $this->getJson('/api/app-notices?installation_id='.$installationId)->assertOk()->assertJsonMissingPath('data.0.unread');
});
