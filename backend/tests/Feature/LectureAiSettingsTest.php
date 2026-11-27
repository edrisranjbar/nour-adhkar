<?php

use App\Models\User;
use App\Services\LectureAiSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('stores the key encrypted and never renders or flashes it', function () {
    $this->actingAs(User::factory()->create(['role'=>'admin','active'=>true]), 'admin');
    $this->put('/admin/settings/lecture-ai', ['base_url'=>'https://ai.manageit.dev/api/v1','api_key'=>'test-private-key'])->assertRedirect();
    expect(DB::table('lecture_ai_settings')->value('api_key'))->not->toBe('test-private-key');
    $this->get('/admin/settings/lecture-ai')->assertOk()->assertDontSee('test-private-key');
    $this->put('/admin/settings/lecture-ai', ['base_url'=>'invalid','api_key'=>'never-flash'])->assertSessionHasErrors('base_url')->assertSessionMissing('_old_input.api_key');
    $this->put('/admin/settings/lecture-ai', ['base_url'=>'https://ai.manageit.dev/api/v1','api_key'=>''])->assertRedirect();
    expect(app(LectureAiSettings::class)->key())->toBe('test-private-key');
});

it('fetches the provider model catalog and permits only confirmed free models', function () {
    $this->actingAs(User::factory()->create(['role'=>'admin','active'=>true]), 'admin');
    $this->put('/admin/settings/lecture-ai', ['base_url'=>'https://ai.manageit.dev/api/v1','api_key'=>'test-key']);
    Http::fake(['*/models'=>Http::response(['data'=>[
        ['id'=>'audio-free','pricing'=>['prompt'=>'0','completion'=>'0']],
        ['id'=>'text-free','pricing'=>['prompt'=>'0','completion'=>'0']],
        ['id'=>'paid','pricing'=>['prompt'=>'1','completion'=>'0']],
    ]])]);
    $this->post('/admin/settings/lecture-ai/models')->assertRedirect();
    $this->put('/admin/settings/lecture-ai', ['base_url'=>'https://ai.manageit.dev/api/v1','transcription_model'=>'paid'])->assertSessionHasErrors();
    $this->put('/admin/settings/lecture-ai', ['base_url'=>'https://ai.manageit.dev/api/v1','transcription_model'=>'audio-free','summary_model'=>'text-free'])->assertRedirect();
    config(['services.lecture_ai.token'=>'worker-token']);
    $this->getJson('/api/internal/lectures/config')->assertUnauthorized();
    $this->withToken('worker-token')->getJson('/api/internal/lectures/config')->assertOk()->assertJsonPath('data.summary_model','text-free')->assertHeader('Cache-Control','no-store, private');
});

it('does not classify unknown pricing or paid audio as free', function () {
    expect(LectureAiSettings::free(['id'=>'unknown']))->toBeFalse()
        ->and(LectureAiSettings::free(['pricing'=>['prompt'=>0,'completion'=>0,'audio'=>1]]))->toBeFalse();
});
