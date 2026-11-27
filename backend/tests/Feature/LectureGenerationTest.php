<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    Storage::disk('public')->put('lectures/test.mp3', 'audio');
    DB::table('lecture_ai_settings')->insert(['id'=>1, 'base_url'=>'https://provider.example/v1',
        'api_key'=>Crypt::encryptString('secret'), 'transcription_model'=>'audio', 'summary_model'=>'text',
        'models'=>json_encode([['id'=>'audio'], ['id'=>'text']])]);
    $this->scholar = DB::table('scholars')->where('slug','ziaei')->value('id');
    $this->lecture = DB::table('lectures')->insertGetId(['scholar_id'=>$this->scholar, 'title'=>'Test',
        'audio_path'=>'lectures/test.mp3', 'description'=>'Original', 'ai_status'=>'queued']);
    $this->url = "/admin/scholars/{$this->scholar}/lectures/{$this->lecture}/generate";
    $this->actingAs(User::factory()->create(['role'=>'admin','active'=>true]), 'admin');
    Http::preventStrayRequests();
});

it('sends provider calls immediately and returns the saved description without a queue', function () {
    Http::fake([
        '*/audio/transcriptions'=>Http::response(['text'=>'Transcript']),
        '*/chat/completions'=>Http::response(['choices'=>[['message'=>['content'=>'Summary']]]]),
    ]);
    $this->postJson($this->url)->assertOk()->assertJsonPath('description',"خلاصه\nSummary\n\nمتن کامل سخنرانی\nTranscript");
    expect(DB::table('lectures')->find($this->lecture)->ai_status)->toBe('done');
    Http::assertSentCount(2);
    $this->get("/admin/scholars/{$this->scholar}/lectures/{$this->lecture}/edit")->assertOk()
        ->assertSee("form.addEventListener('submit'", false)->assertDontSee('setInterval', false);
});

it('surfaces provider failures without leaking response bodies or losing the description', function () {
    Http::fake(['*/audio/transcriptions'=>Http::response('secret-provider-message', 401)]);
    $this->postJson($this->url)->assertStatus(422)->assertDontSee('secret-provider-message');
    $row=DB::table('lectures')->find($this->lecture);
    expect($row->ai_status)->toBe('failed')->and($row->description)->toBe('Original');
});

it('requires admin login and prevents simultaneous generation', function () {
    $lock=\Illuminate\Support\Facades\Cache::lock('lecture-description:'.$this->lecture,70);
    $lock->get();
    try { $this->postJson($this->url)->assertStatus(409); } finally { $lock->release(); }
    Http::assertNothingSent();
});
