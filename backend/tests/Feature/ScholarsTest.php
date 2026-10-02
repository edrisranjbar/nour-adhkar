<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function scholarsAdmin(): User
{
    return User::factory()->create(['role' => 'admin', 'active' => true]);
}

beforeEach(fn () => Storage::fake('public'));

it('returns the saved lecture order for ajax moves and keeps moves inside their scholar', function () {
    $this->actingAs(scholarsAdmin(), 'admin');
    $scholar = DB::table('scholars')->where('slug', 'ziaei')->value('id');
    $other = DB::table('scholars')->where('slug', 'pordel')->value('id');
    foreach (['اول', 'دوم', 'سوم'] as $title) {
        $this->post("/admin/scholars/$scholar/lectures", [
            'title' => $title, 'audio_url' => 'https://example.com/lecture.mp3', 'published' => 1,
        ])->assertRedirect();
    }
    $ids = DB::table('lectures')->where('scholar_id', $scholar)->orderBy('id')->pluck('id')->all();
    $move = "/admin/scholars/$scholar/lectures/{$ids[1]}/move";
    $this->patchJson("$move/up")->assertOk()->assertExactJson(['order' => [$ids[1], $ids[0], $ids[2]]]);
    // Already first: a no-op still returns the actual order.
    $this->patchJson("$move/up")->assertOk()->assertExactJson(['order' => [$ids[1], $ids[0], $ids[2]]]);
    $this->patchJson("$move/down")->assertOk()->assertExactJson(['order' => $ids]);
    expect(DB::table('lectures')->where('scholar_id', $scholar)->orderBy('sort_order')->pluck('id')->all())->toBe($ids);
    $this->patchJson("/admin/scholars/$other/lectures/{$ids[0]}/move/up")->assertNotFound();
    $this->patchJson("$move/invalid")->assertNotFound();
    $this->get("/admin/scholars/$scholar/lectures")->assertOk()->assertSee('data-move="up"', false);
});

it('requires panel login for ajax lecture moves', function () {
    $this->patchJson('/admin/scholars/1/lectures/1/move/up')->assertRedirect(route('panel.login'));
});

it('serves the seeded scholars in the shape the app parses', function () {
    $this->getJson('/api/scholars')->assertOk()
        ->assertJsonPath('data.0.id', 'ziaei')
        ->assertJsonPath('data.0.name', 'شیخ ضیایی')
        ->assertJsonPath('data.0.hue', 152)
        ->assertJsonPath('data.0.lectures', [])
        ->assertJsonPath('data.1.id', 'pordel');
});

it('lets an admin add a scholar and lectures by upload or link, in order, published only', function () {
    $admin = scholarsAdmin();
    $this->actingAs($admin, 'admin');

    $this->post('/admin/scholars', ['name' => 'استاد تازه', 'slug' => 'new-one', 'tagline' => 'دروس', 'hue' => 40, 'published' => 1])
        ->assertRedirect(route('panel.scholars.index'));
    $id = DB::table('scholars')->where('slug', 'new-one')->value('id');

    $this->post("/admin/scholars/$id/lectures", [
        'title' => 'جلسه اول', 'audio_file' => UploadedFile::fake()->create('one.mp3', 500, 'audio/mpeg'),
        'duration_min' => 12, 'duration_sec_part' => 5, 'published' => 1,
    ])->assertRedirect();
    $this->post("/admin/scholars/$id/lectures", [
        'title' => 'جلسه دوم', 'audio_url' => 'https://cdn.example.com/two.mp3', 'published' => 1,
    ])->assertRedirect();
    $this->post("/admin/scholars/$id/lectures", [
        'title' => 'پیش‌نویس', 'audio_url' => 'https://cdn.example.com/draft.mp3',
    ])->assertRedirect();

    $first = DB::table('lectures')->where('title', 'جلسه اول')->first();
    Storage::disk('public')->assertExists($first->audio_path);

    $scholar = collect($this->getJson('/api/scholars')->json('data'))->firstWhere('id', 'new-one');
    expect($scholar['lectures'])->toHaveCount(2)
        ->and($scholar['lectures'][0]['title'])->toBe('جلسه اول')
        ->and($scholar['lectures'][0]['audioUrl'])->toStartWith('http')->toEndWith('.mp3')
        ->and($scholar['lectures'][0]['durationSec'])->toBe(725)
        ->and($scholar['lectures'][1]['audioUrl'])->toBe('https://cdn.example.com/two.mp3')
        ->and($scholar['lectures'][1])->not->toHaveKey('durationSec');

    // Move the second lecture up; the API order follows.
    $second = DB::table('lectures')->where('title', 'جلسه دوم')->value('id');
    $this->patch("/admin/scholars/$id/lectures/$second/move/up")->assertRedirect();
    $scholar = collect($this->getJson('/api/scholars')->json('data'))->firstWhere('id', 'new-one');
    expect($scholar['lectures'][0]['title'])->toBe('جلسه دوم');

    // The lectures table shows each lecture's position and offers sortable headers.
    $this->get("/admin/scholars/$id/lectures")
        ->assertOk()
        ->assertSee('id="lectures-table"', false)
        ->assertSee('data-sort="title"', false)
        ->assertSee('data-pos="1" data-title="جلسه دوم"', false)
        ->assertSee('data-pos="2" data-title="جلسه اول"', false);
});

it('requires audio, validates links and slugs, and hides unpublished scholars', function () {
    $this->actingAs(scholarsAdmin(), 'admin');
    $ziaei = DB::table('scholars')->where('slug', 'ziaei')->value('id');

    $this->post("/admin/scholars/$ziaei/lectures", ['title' => 'بی‌صوت'])->assertSessionHasErrors('audio_file');
    $this->post("/admin/scholars/$ziaei/lectures", ['title' => 'x', 'audio_url' => 'http://insecure.example/a.mp3'])
        ->assertSessionHasErrors('audio_url');
    $this->post('/admin/scholars', ['name' => 'x', 'slug' => 'ziaei', 'hue' => 1])->assertSessionHasErrors('slug');
    $this->post('/admin/scholars', ['name' => 'x', 'slug' => 'Bad Slug', 'hue' => 1])->assertSessionHasErrors('slug');

    $this->patch("/admin/scholars/$ziaei/toggle")->assertRedirect();
    expect(collect($this->getJson('/api/scholars')->json('data'))->pluck('id')->all())->toBe(['pordel']);
});

it('deletes uploaded audio with its lecture and with its scholar', function () {
    $this->actingAs(scholarsAdmin(), 'admin');
    $id = DB::table('scholars')->where('slug', 'pordel')->value('id');
    foreach (['a', 'b'] as $name) {
        $this->post("/admin/scholars/$id/lectures", ['title' => $name, 'audio_file' => UploadedFile::fake()->create("$name.mp3", 100, 'audio/mpeg')]);
    }
    [$a, $b] = DB::table('lectures')->orderBy('id')->get()->all();

    $this->delete("/admin/scholars/$id/lectures/{$a->id}")->assertRedirect();
    Storage::disk('public')->assertMissing($a->audio_path);

    $this->delete("/admin/scholars/$id")->assertRedirect();
    Storage::disk('public')->assertMissing($b->audio_path);
    expect(DB::table('lectures')->count())->toBe(0);
});

it('keeps the admin pages behind the panel login', function () {
    $this->get('/admin/scholars')->assertRedirect(route('panel.login'));
    $this->actingAs(scholarsAdmin(), 'admin')->get('/admin/scholars')->assertOk()->assertSee('شیخ ضیایی');
    $id = DB::table('scholars')->where('slug', 'ziaei')->value('id');
    $this->get("/admin/scholars/$id/lectures")->assertOk()->assertSee('به‌زودی');
    $this->get("/admin/scholars/$id/lectures/create")->assertOk();
    $this->get('/admin/scholars/create')->assertOk();
});

it('renames Sheikh Pordel and serves no photo until one is uploaded', function () {
    $pordel = collect($this->getJson('/api/scholars')->json('data'))->firstWhere('id', 'pordel');
    expect($pordel['name'])->toBe('شیخ محمد صالح پردل')->and($pordel['photoUrl'])->toBeNull();
});

it('uploads, replaces and removes a scholar photo, and deletes it with the scholar', function () {
    $this->actingAs(scholarsAdmin(), 'admin');
    $scholar = DB::table('scholars')->where('slug', 'ziaei')->first();
    $fields = ['name' => $scholar->name, 'slug' => 'ziaei', 'tagline' => $scholar->tagline, 'hue' => $scholar->hue, 'published' => 1];

    $this->put("/admin/scholars/{$scholar->id}", $fields + ['photo' => UploadedFile::fake()->image('a.jpg', 400, 400)])->assertRedirect();
    $first = DB::table('scholars')->find($scholar->id)->photo_path;
    Storage::disk('public')->assertExists($first);
    $ziaei = collect($this->getJson('/api/scholars')->json('data'))->firstWhere('id', 'ziaei');
    expect($ziaei['photoUrl'])->toStartWith('http')->toContain('/storage/scholars/');

    // Saving without a new file keeps the photo; a new file replaces the old one.
    $this->put("/admin/scholars/{$scholar->id}", $fields)->assertRedirect();
    expect(DB::table('scholars')->find($scholar->id)->photo_path)->toBe($first);
    $this->put("/admin/scholars/{$scholar->id}", $fields + ['photo' => UploadedFile::fake()->image('b.png', 300, 300)])->assertRedirect();
    $second = DB::table('scholars')->find($scholar->id)->photo_path;
    Storage::disk('public')->assertMissing($first);
    Storage::disk('public')->assertExists($second);

    // Non-images are rejected.
    $this->put("/admin/scholars/{$scholar->id}", $fields + ['photo' => UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')])
        ->assertSessionHasErrors('photo');

    $this->put("/admin/scholars/{$scholar->id}", $fields + ['remove_photo' => 1])->assertRedirect();
    Storage::disk('public')->assertMissing($second);
    expect(DB::table('scholars')->find($scholar->id)->photo_path)->toBeNull();

    $this->put("/admin/scholars/{$scholar->id}", $fields + ['photo' => UploadedFile::fake()->image('c.webp', 200, 200)]);
    $third = DB::table('scholars')->find($scholar->id)->photo_path;
    $this->delete("/admin/scholars/{$scholar->id}")->assertRedirect();
    Storage::disk('public')->assertMissing($third);
});
