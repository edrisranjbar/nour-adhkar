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

it('recovers from 413 with ordered compressed parts and saves only the complete transcript', function () {
    $this->mock(\App\Services\LectureAudioChunks::class, function ($mock) {
        $mock->shouldReceive('prepare')->once()->andReturnUsing(function ($source, $directory) {
            file_put_contents($directory.'/part-00000.mp3', 'part1');
            file_put_contents($directory.'/part-00001.mp3', 'part2');
            return [$directory.'/part-00000.mp3', $directory.'/part-00001.mp3'];
        });
    });
    Http::fakeSequence()->push('', 413)->push(['text'=>'First'])->push(['text'=>'Second'])
        ->push(['choices'=>[['message'=>['content'=>'Summary']]]]);
    $this->postJson($this->url)->assertOk();
    expect(DB::table('lectures')->find($this->lecture)->transcript)->toBe("First\n\nSecond");
    Http::assertSentCount(4);
});

it('keeps the description if a compressed part is still rejected', function () {
    $this->mock(\App\Services\LectureAudioChunks::class, function ($mock) {
        $mock->shouldReceive('prepare')->andReturnUsing(function ($source, $directory) {
            file_put_contents($directory.'/part-00000.mp3', 'part');
            return [$directory.'/part-00000.mp3'];
        });
    });
    Http::fakeSequence()->push('', 413)->push('', 413);
    $this->postJson($this->url)->assertStatus(422)->assertSee('413');
    expect(DB::table('lectures')->find($this->lecture)->description)->toBe('Original');
});

it('sends MiMo audio through chat completions with the public URL and no multipart upload', function () {
    DB::table('lecture_ai_settings')->where('id',1)->update(['transcription_model'=>'manageit/mimo-v2.6-flash',
        'models'=>json_encode([['id'=>'manageit/mimo-v2.6-flash'],['id'=>'text']])]);
    DB::table('lectures')->where('id',$this->lecture)->update(['audio_path'=>null,'audio_url'=>'https://audio.example/lecture.mp3']);
    Http::fakeSequence()
        ->push(['choices'=>[['message'=>['content'=>'Transcript'],'finish_reason'=>'stop']]])
        ->push(['choices'=>[['message'=>['content'=>'Summary']]]]);
    $this->postJson($this->url)->assertOk();
    Http::assertSent(fn ($r) => $r->url() === 'https://provider.example/v1/chat/completions'
        && $r['model'] === 'manageit/mimo-v2.6-flash'
        && $r['messages'][0]['content'][0]['input_audio']['data'] === 'https://audio.example/lecture.mp3');
    Http::assertNotSent(fn ($r) => str_contains($r->url(),'/audio/transcriptions'));
    Http::assertSentCount(2);
});

it('shows the provider request ID when MiMo chat audio is rejected', function () {
    DB::table('lecture_ai_settings')->where('id',1)->update(['transcription_model'=>'manageit/mimo-v2.6-flash',
        'models'=>json_encode([['id'=>'manageit/mimo-v2.6-flash'],['id'=>'text']])]);
    DB::table('lectures')->where('id',$this->lecture)->update(['audio_path'=>null,'audio_url'=>'https://audio.example/lecture.mp3']);
    Http::fakeSequence()->push('private provider error',404,['x-request-id'=>'request-123']);
    $this->postJson($this->url)->assertStatus(422)->assertSee('request-123')->assertDontSee('private provider error');
    expect(DB::table('lectures')->find($this->lecture)->description)->toBe('Original');
});

it('accepts completed MiMo output even when the gateway lowered its token allowance', function () {
    DB::table('lecture_ai_settings')->where('id',1)->update(['transcription_model'=>'manageit/mimo-v2.6-flash',
        'models'=>json_encode([['id'=>'manageit/mimo-v2.6-flash'],['id'=>'text']])]);
    DB::table('lectures')->where('id',$this->lecture)->update(['audio_path'=>null,'audio_url'=>'https://audio.example/lecture.mp3']);
    Http::fakeSequence()
        ->push(['choices'=>[['message'=>['content'=>'Complete transcript'],'finish_reason'=>'stop']]],200,['x-manageit-output-capped'=>'1024'])
        ->push(['choices'=>[['message'=>['content'=>'Complete summary'],'finish_reason'=>'stop']]],200,['x-manageit-output-capped'=>'1024']);
    $this->postJson($this->url)->assertOk();
    expect(DB::table('lectures')->find($this->lecture)->transcript)->toBe('Complete transcript');
});

it('rejects actual truncation without replacing the saved description', function () {
    DB::table('lecture_ai_settings')->where('id',1)->update(['transcription_model'=>'manageit/mimo-v2.6-flash',
        'models'=>json_encode([['id'=>'manageit/mimo-v2.6-flash'],['id'=>'text']])]);
    DB::table('lectures')->where('id',$this->lecture)->update(['audio_path'=>null,'audio_url'=>'https://audio.example/lecture.mp3']);
    Http::fakeSequence()->push(['choices'=>[['message'=>['content'=>'Partial'],'finish_reason'=>'length']]]);
    $this->postJson($this->url)->assertStatus(422)->assertSee('finish_reason=length');
    expect(DB::table('lectures')->find($this->lecture)->description)->toBe('Original');
    Http::assertSentCount(1);
});

it('does not save a summary that actually hit its output limit', function () {
    Http::fakeSequence()->push(['text'=>'Complete transcript'])
        ->push(['choices'=>[['message'=>['content'=>'Partial summary'],'finish_reason'=>'length']]]);
    $this->postJson($this->url)->assertStatus(422);
    expect(DB::table('lectures')->find($this->lecture)->description)->toBe('Original');
});
