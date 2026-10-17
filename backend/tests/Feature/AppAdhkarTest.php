<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function appAdhkarAdmin(): User
{
    return User::factory()->create(['role' => 'admin', 'active' => true]);
}

it('lets an admin add to a built-in collection and create a new one, served in the shape the app parses', function () {
    $this->actingAs(appAdhkarAdmin(), 'admin');

    $this->post('/admin/app-adhkar', ['kind' => 'builtin', 'builtin_key' => 'morning', 'icon' => 'menu_book', 'published' => 1])->assertRedirect();
    $morning = DB::table('app_dhikr_collections')->where('builtin_key', 'morning')->value('id');
    $this->post("/admin/app-adhkar/$morning/dhikrs", [
        'arabic_text' => 'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ', 'translation' => 'پاک است خدا و ستایش از آن اوست',
        'source' => 'صحیح مسلم', 'target_count' => 100, 'published' => 1,
    ])->assertRedirect(route('panel.app-dhikrs.index', $morning));
    $this->post("/admin/app-adhkar/$morning/dhikrs", ['arabic_text' => 'پنهان', 'target_count' => 1])->assertRedirect();

    $this->post('/admin/app-adhkar', ['kind' => 'new', 'title' => 'دعای باران', 'description' => 'هنگام باران', 'icon' => 'mosque', 'published' => 1])->assertRedirect();
    $rain = DB::table('app_dhikr_collections')->where('title', 'دعای باران')->value('id');
    $this->post("/admin/app-adhkar/$rain/dhikrs", ['arabic_text' => 'اللَّهُمَّ صَيِّبًا نَافِعًا', 'target_count' => 1, 'published' => 1])->assertRedirect();

    // An empty new collection is not sent to the app.
    $this->post('/admin/app-adhkar', ['kind' => 'new', 'title' => 'خالی', 'icon' => 'menu_book', 'published' => 1])->assertRedirect();

    $this->get('/admin/app-adhkar')->assertOk()->assertSee('اذکار صبحگاه')->assertSee('دعای باران');

    $this->getJson('/api/app-adhkar')->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.key', 'morning')
        ->assertJsonPath('data.0.title', null)
        ->assertJsonPath('data.0.items.0.arabic', 'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ')
        ->assertJsonPath('data.0.items.0.count', 100)
        ->assertJsonCount(1, 'data.0.items')
        ->assertJsonPath('data.1.key', null)
        ->assertJsonPath('data.1.title', 'دعای باران')
        ->assertJsonPath('data.1.icon', 'mosque');
});

it('answers 304 until something changes', function () {
    $this->actingAs(appAdhkarAdmin(), 'admin');
    $etag = $this->getJson('/api/app-adhkar')->assertOk()->assertJsonPath('data', [])->headers->get('ETag');
    $this->getJson('/api/app-adhkar', ['If-None-Match' => $etag])->assertStatus(304);

    $this->post('/admin/app-adhkar', ['kind' => 'builtin', 'builtin_key' => 'evening', 'icon' => 'menu_book', 'published' => 1]);
    $this->getJson('/api/app-adhkar', ['If-None-Match' => $etag])->assertOk();
});

it('rejects unknown built-in collections and requires a title for new ones', function () {
    $this->actingAs(appAdhkarAdmin(), 'admin');
    $this->post('/admin/app-adhkar', ['kind' => 'builtin', 'builtin_key' => 'nope', 'icon' => 'menu_book'])->assertSessionHasErrors('builtin_key');
    $this->post('/admin/app-adhkar', ['kind' => 'new', 'title' => '', 'icon' => 'menu_book'])->assertSessionHasErrors('title');
    expect(DB::table('app_dhikr_collections')->count())->toBe(0);
});

it('requires panel login', function () {
    $this->get('/admin/app-adhkar')->assertRedirect(route('panel.login'));
});
