<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['jwt.secret' => str_repeat('test-only-instagram-queue-', 3)]);
});

it('restricts the content queue to admins', function () {
    $this->getJson('/api/admin/instagram-queue')->assertUnauthorized();
    $this->actingAs(User::factory()->create(['role' => 'user']), 'api')
        ->getJson('/api/admin/instagram-queue')->assertForbidden();
    $this->postJson('/api/admin/instagram-templates', [])->assertForbidden();
});

it('saves reusable templates and preserves post designs across template edits and deletion', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']), 'api');
    $templates = $this->getJson('/api/admin/instagram-queue')->assertOk()->json('templates');
    $design = $templates[0]['design'];
    $design['background'] = '#112233';
    $design['text'] = '#FFFFFF';
    $design['font'] = 'noto-naskh';
    $design['layout'] = 'framed';
    $template = $this->postJson('/api/admin/instagram-templates', ['name' => 'Custom', 'design' => $design])->assertCreated()->json('data');
    $id = $this->postJson('/api/admin/instagram-queue', ['verse_key' => '94:5'])->json('data.id');
    $this->putJson("/api/admin/instagram-queue/$id", ['template_id' => $template['id']])->assertOk()->assertJsonPath('data.design.background', '#112233')->assertJsonPath('data.design.font', 'noto-naskh');
    $design['background'] = '#445566';
    $this->putJson("/api/admin/instagram-templates/{$template['id']}", ['name' => 'Changed', 'design' => $design])->assertOk();
    $this->putJson("/api/admin/instagram-queue/$id", ['caption' => 'Updated caption'])->assertOk()->assertJsonPath('data.design.background', '#112233');
    $this->deleteJson("/api/admin/instagram-templates/{$template['id']}")->assertNoContent();
    $this->getJson('/api/admin/instagram-queue')->assertJsonPath('data.0.template_id', null)->assertJsonPath('data.0.design.background', '#112233');
    $this->putJson("/api/admin/instagram-queue/$id", ['template_id' => $template['id']])->assertUnprocessable();
});

it('uses the chosen default for new posts and validates template settings', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']), 'api');
    $templates = $this->getJson('/api/admin/instagram-queue')->json('templates');
    $initial = $templates[0];
    $this->deleteJson("/api/admin/instagram-templates/{$initial['id']}")->assertUnprocessable();
    $design = $initial['design'];
    $invalid = $design; $invalid['background'] = 'url(javascript:alert(1))';
    $this->postJson('/api/admin/instagram-templates', ['name' => 'Bad color', 'design' => $invalid])->assertUnprocessable();
    $invalid = $design; $invalid['font'] = 'external';
    $this->postJson('/api/admin/instagram-templates', ['name' => 'Bad font', 'design' => $invalid])->assertUnprocessable();
    $invalid = $design; $invalid['font_size'] = 300;
    $this->postJson('/api/admin/instagram-templates', ['name' => 'Bad size', 'design' => $invalid])->assertUnprocessable();
    $template = $this->postJson('/api/admin/instagram-templates', ['name' => 'Default', 'design' => $design, 'is_default' => true])->assertCreated()->json('data');
    expect(DB::table('instagram_templates')->where('is_default', true)->count())->toBe(1);
    $this->postJson('/api/admin/instagram-queue', ['verse_key' => '2:152'])->assertCreated()->assertJsonPath('data.template_id', $template['id']);
    $this->deleteJson("/api/admin/instagram-templates/{$initial['id']}")->assertNoContent();
    $this->putJson("/api/admin/instagram-templates/{$template['id']}", ['name' => 'Default', 'design' => $design, 'is_default' => false])->assertUnprocessable();
});

it('creates source-backed drafts idempotently and requires review before recording publication', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']), 'api');
    $this->getJson('/api/admin/instagram-queue')->assertOk()->assertJsonCount(8, 'catalog');
    $this->postJson('/api/admin/instagram-queue', ['verse_key' => '999:1'])->assertUnprocessable();
    $id = $this->postJson('/api/admin/instagram-queue', ['verse_key' => '94:5'])->assertCreated()->json('data.id');
    $this->postJson('/api/admin/instagram-queue', ['verse_key' => '94:5'])->assertOk()->assertJsonPath('data.id', $id);
    expect(DB::table('instagram_posts')->count())->toBe(1);
    $this->putJson("/api/admin/instagram-queue/$id", ['status' => 'published', 'instagram_url' => 'https://www.instagram.com/p/Example/'])->assertUnprocessable();
    $this->putJson("/api/admin/instagram-queue/$id", ['status' => 'queued'])->assertOk();
    $this->putJson("/api/admin/instagram-queue/$id", ['status' => 'published'])->assertUnprocessable();
    $this->putJson("/api/admin/instagram-queue/$id", ['status' => 'published', 'instagram_url' => 'https://evil.example/p/Example/'])->assertUnprocessable();
    $this->putJson("/api/admin/instagram-queue/$id", ['status' => 'published', 'instagram_url' => 'https://www.instagram.com/p/Example/'])->assertOk()->assertJsonPath('data.status', 'published');
    expect(DB::table('instagram_posts')->where('id', $id)->value('published_at'))->not->toBeNull();
    $this->putJson("/api/admin/instagram-queue/$id", ['status' => 'draft'])->assertUnprocessable();
    $this->deleteJson("/api/admin/instagram-queue/$id")->assertUnprocessable();
});

it('reorders the complete ready queue and rejects stale or duplicate orders', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']), 'api');
    $ids = [];
    foreach (['94:5', '2:152'] as $key) {
        $id = $this->postJson('/api/admin/instagram-queue', ['verse_key' => $key])->json('data.id');
        $this->putJson("/api/admin/instagram-queue/$id", ['status' => 'queued'])->assertOk();
        $ids[] = $id;
    }
    $this->putJson('/api/admin/instagram-queue/order', ['ids' => [$ids[0]]])->assertConflict();
    $this->putJson('/api/admin/instagram-queue/order', ['ids' => [$ids[0], $ids[0]]])->assertUnprocessable();
    $this->putJson('/api/admin/instagram-queue/order', ['ids' => array_reverse($ids)])->assertOk();
    $this->getJson('/api/admin/instagram-queue')->assertJsonPath('data.0.id', $ids[1]);
    $this->putJson("/api/admin/instagram-queue/{$ids[1]}", ['status' => 'draft'])->assertOk();
    $this->putJson('/api/admin/instagram-queue/order', ['ids' => array_reverse($ids)])->assertConflict();
});
