<?php

use App\Models\User;
use App\Services\StoreStats\BazaarInstallProvider;
use App\Services\StoreStats\StoreStats;
use App\Support\PanelFormat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/** The part of the public Bazaar page that holds the install count, as the server renders it. */
function bazaarPage(string $installs): string
{
    return '<table class="InfoCubes"><tbody class="InfoCubes__table">'
        .'<tr class="InfoCube"><td class="InfoCube__title fs-12">نصب</td><td class="InfoCube__content fs-14"><!--[-->'.$installs.' <!--]--></td></tr>'
        .'<!----><tr class="InfoCube InfoCube--link"><td class="InfoCube__title fs-12">از ۲۱ رأی</td><td class="InfoCube__content fs-14"><!--[--><div>۵</div><!--]--></td></tr>'
        .'</tbody></table>';
}

function installAdmin(): User
{
    return User::factory()->create(['role' => 'admin', 'active' => true]);
}

beforeEach(function () {
    Cache::flush();
    Http::fake(['api.cafebazaar.ir/*' => Http::response([
        'properties' => ['statusCode' => 200], 'singleReply' => ['reviewReply' => ['reviews' => []]],
    ])]);
});

it('charts overall totals using Tehran days and preserves corrections and carried totals', function () {
    $this->travelTo(\Carbon\Carbon::parse('2026-10-03 12:00:00', 'Asia/Tehran'));
    foreach ([
        ['2026-09-29 12:00:00', 500], // Seed predates the three-day window.
        ['2026-10-01 10:00:00', 550],
        ['2026-10-01 20:45:00', 570], // October 2 in Tehran.
        ['2026-10-01 20:45:00', 575], // Later ID wins at the same timestamp.
        ['2026-10-01 21:00:00', 560], // Preserve a store correction.
        ['2026-10-03 21:00:00', 999], // Tomorrow in Tehran: outside the window.
    ] as [$at, $total]) {
        DB::table('store_install_snapshots')->insert(['store' => 'bazaar', 'installs' => $total, 'recorded_at' => $at]);
    }

    expect(app(StoreStats::class)->daily(3))->toBe([
        'days' => ['2026-10-01', '2026-10-02', '2026-10-03'],
        'stores' => ['bazaar' => [550, 560, 560]],
    ]);
    expect(app(StoreStats::class)->daily(4)['stores']['bazaar'])->toBe([500, 550, 560, 560]);
});

it('keeps days before the first recorded total unavailable', function () {
    $this->travelTo(\Carbon\Carbon::parse('2026-10-03 12:00:00', 'Asia/Tehran'));
    DB::table('store_install_snapshots')->insert([
        'store' => 'bazaar', 'installs' => 550, 'recorded_at' => '2026-10-02 09:00:00',
    ]);
    expect(app(StoreStats::class)->daily(3)['stores']['bazaar'])->toBe([null, 550, 550]);
});

it('shows recent users by name and Tehran date without email or time', function () {
    $admin = installAdmin();
    $user = User::factory()->create([
        'name' => 'کاربر آزمایشی', 'email' => 'recent-only@example.com',
        'created_at' => '2026-10-02 22:15:00',
    ]);
    $html = $this->actingAs($admin, 'admin')->get('/admin')->assertOk()->getContent();
    $start = strpos($html, '<h2>کاربران تازه</h2>');
    $end = strpos($html, '<h2>توضیح سخنرانی‌ها', $start);
    $recent = substr($html, $start, $end - $start);
    expect($recent)->toContain('کاربر آزمایشی')
        ->toContain(PanelFormat::calendarDate($user->created_at->copy()->setTimezone('Asia/Tehran')))
        ->not->toContain('recent-only@example.com')
        ->not->toContain('۰۱:۴۵')
        ->not->toContain(' – ');
});

it('reads the install count in Persian, Arabic and Latin digits and with store suffixes', function (string $text, int $expected) {
    expect(BazaarInstallProvider::parseCount($text))->toBe($expected);
})->with([
    ['۵۵۰', 550],
    ['٥٥٠', 550],
    ['550', 550],
    ['۱٬۲۵۰', 1250],
    ['۱,۲۵۰', 1250],
    ['۱۲ هزار', 12000],
    ['۱٫۵ میلیون', 1500000],
    ['+۱۰۰', 100],
    ['۱۰۰+', 100],
]);

it('refuses text that is not a count', function () {
    expect(BazaarInstallProvider::parseCount('بدون عدد'))->toBeNull()
        ->and(BazaarInstallProvider::parseCount(''))->toBeNull();
});

it('finds the installs cell on the page and ignores the rating cell', function () {
    expect(BazaarInstallProvider::parseInstalls(bazaarPage('۵۵۰')))->toBe(550)
        ->and(BazaarInstallProvider::parseInstalls('<html>no table here</html>'))->toBeNull();
});

it('serves the count and chart to an admin, calling Bazaar once per cache window', function () {
    Http::fake(['cafebazaar.ir/*' => Http::response(bazaarPage('۵۵۰'))]);
    $admin = installAdmin();

    $this->actingAs($admin, 'admin')->getJson('/admin/installs')
        ->assertOk()
        ->assertJsonPath('stores.bazaar.installs', 550)
        ->assertJsonPath('stores.bazaar.stale', false)
        ->assertJsonPath('stores.bazaar.label', 'کافه‌بازار')
        ->assertJsonPath('stores.bazaar.rating', 5)
        ->assertJsonPath('stores.bazaar.rating_count', 21)
        ->assertJsonPath('stores.bazaar.rating_stale', false)
        ->assertJsonPath('feedback', 0)
        ->assertJsonPath('interval', 600)
        ->assertJsonCount(14, 'daily.stores.bazaar')
        ->assertJsonPath('daily.stores.bazaar.13', 550);

    $this->actingAs($admin, 'admin')->getJson('/admin/installs')->assertOk()->assertJsonPath('stores.bazaar.installs', 550);

    Http::assertSentCount(2);
    expect(DB::table('store_install_snapshots')->count())->toBe(1);
});

it('uses the package from the settings in the Bazaar address', function () {
    config(['stores.package' => 'com.example.other']);
    Http::fake(['cafebazaar.ir/*' => Http::response(bazaarPage('۷'))]);

    $this->actingAs(installAdmin(), 'admin')->getJson('/admin/installs')->assertOk();

    Http::assertSent(fn ($request) => $request->url() === 'https://cafebazaar.ir/app/com.example.other');
});

it('records a new snapshot only when the number changes or the heartbeat passes', function () {
    $admin = installAdmin();
    $page = bazaarPage('۵۵۰');
    Http::fake(['cafebazaar.ir/*' => function () use (&$page) {
        return Http::response($page);
    }]);
    $this->actingAs($admin, 'admin')->getJson('/admin/installs')->assertOk();

    Cache::flush();   // the next request fetches again, with the same number
    $this->actingAs($admin, 'admin')->getJson('/admin/installs')->assertOk();
    expect(DB::table('store_install_snapshots')->count())->toBe(1);

    Cache::flush();
    $page = bazaarPage('۵۵۳');
    $this->actingAs($admin, 'admin')->getJson('/admin/installs')->assertOk()->assertJsonPath('stores.bazaar.installs', 553);
    expect(DB::table('store_install_snapshots')->count())->toBe(2);

    Cache::flush();
    $this->travel(16)->minutes();
    $response = $this->actingAs($admin, 'admin')->getJson('/admin/installs')->assertOk();
    expect(DB::table('store_install_snapshots')->count())->toBe(3);
    expect($response->json('daily.stores.bazaar'))->toHaveCount(14)
        ->and($response->json('daily.stores.bazaar.13'))->toBe(553);
});

it('falls back to the last known number and marks it stale when Bazaar cannot be read', function () {
    $admin = installAdmin();
    $page = bazaarPage('۵۵۰');
    $status = 200;
    Http::fake(['cafebazaar.ir/*' => function () use (&$page, &$status) {
        return Http::response($page, $status);
    }]);
    $this->actingAs($admin, 'admin')->getJson('/admin/installs')->assertOk();

    Cache::flush();
    $page = '';
    $status = 503;
    $this->actingAs($admin, 'admin')->getJson('/admin/installs')
        ->assertOk()
        ->assertJsonPath('stores.bazaar.installs', 550)
        ->assertJsonPath('stores.bazaar.stale', true);

    Cache::flush();
    $page = '<html>layout changed</html>';
    $status = 200;
    $this->actingAs($admin, 'admin')->getJson('/admin/installs')
        ->assertJsonPath('stores.bazaar.installs', 550)
        ->assertJsonPath('stores.bazaar.stale', true);
});

it('shows no number and no history before the first successful read', function () {
    Http::fake(['cafebazaar.ir/*' => Http::response('', 500)]);

    $this->actingAs(installAdmin(), 'admin')->getJson('/admin/installs')
        ->assertOk()
        ->assertJsonPath('stores.bazaar.installs', null)
        ->assertJsonPath('stores.bazaar.stale', true)
        ->assertJsonPath('stores.bazaar.rating', null)
        ->assertJsonPath('stores.bazaar.rating_count', null)
        ->assertJsonPath('stores.bazaar.rating_stale', true)
        ->assertJsonPath('daily.stores.bazaar', array_fill(0, 14, null));
});

it('does not hit the network for guests and keeps the feed admin-only', function () {
    Http::fake();

    $this->getJson('/admin/installs')->assertRedirect(route('panel.login'));
    Http::assertNothingSent();
});

it('puts the live install card on the dashboard', function () {
    $this->actingAs(installAdmin(), 'admin')->get('/admin')
        ->assertOk()
        ->assertSee('id="installs"', false)
        ->assertSee(route('panel.installs'), false)
        ->assertSee('id="inst-sound"', false)
        ->assertSee('id="bazaar-votes"', false)
        ->assertSee('id="bazaar-stars"', false)
        ->assertSee('id="bazaar-rating"', false)
        ->assertSeeInOrder(['class="kpis"', 'id="installs"', 'آخرین بازخوردها'], false);
});

it('shows an icon beside every sidebar item and the logout button', function () {
    $html = $this->actingAs(installAdmin(), 'admin')->get('/admin')->assertOk()->getContent();

    preg_match('~<nav>(.*?)</nav>~s', $html, $nav);
    expect(substr_count($nav[1], '<a '))->toBe(9)
        ->and(substr_count($nav[1], 'class="ic"'))->toBe(9)
        ->and($html)->toMatch('~<button class="btn sm" type="submit"><svg class="ic"~');
});

it('parses the public vote count and rating without mistaking them for installs', function () {
    $page = str_replace(['۲۱ رأی', '<div>۵</div>'], ['۱٬۲۵۰ رأی', '<div>۴٫۷</div>'], bazaarPage('۵۵۰'));
    expect(BazaarInstallProvider::parseRating($page))->toBe(['rating' => 4.7, 'rating_count' => 1250])
        ->and(BazaarInstallProvider::parseInstalls($page))->toBe(550)
        ->and(BazaarInstallProvider::parseRating(str_replace('۴٫۷', '6', $page)))
        ->toBe(['rating' => null, 'rating_count' => null])
        ->and(BazaarInstallProvider::parseRating('<html>no rating</html>'))
        ->toBe(['rating' => null, 'rating_count' => null]);
});

it('refreshes all listing metrics after ten minutes and updates the in-app feedback count', function () {
    $page = bazaarPage('۵۵۰');
    Http::fake(['cafebazaar.ir/*' => fn () => Http::response($page)]);
    $admin = installAdmin();
    $this->actingAs($admin, 'admin')->getJson('/admin/installs')->assertOk();
    $this->travel(9)->minutes();
    DB::table('app_feedback')->insert(['type' => 'suggestion', 'message' => 'Hello', 'created_at' => now(), 'updated_at' => now()]);
    $this->getJson('/admin/installs')->assertJsonPath('feedback', 1);
    Http::assertSentCount(2);
    $this->travel(1)->minutes();
    $this->getJson('/admin/installs')->assertOk();
    Http::assertSentCount(4);
    expect(DB::table('store_rating_snapshots')->count())->toBe(2);
});

it('preserves rating and votes independently when only installs can still be read', function () {
    $page = bazaarPage('۵۵۰');
    Http::fake(['cafebazaar.ir/*' => function () use (&$page) {
        return Http::response($page);
    }]);
    $this->actingAs(installAdmin(), 'admin')->getJson('/admin/installs')->assertOk();
    Cache::flush();
    $page = str_replace('از ۲۱ رأی', 'changed layout', bazaarPage('۶۰۰'));
    $this->getJson('/admin/installs')
        ->assertJsonPath('stores.bazaar.installs', 600)
        ->assertJsonPath('stores.bazaar.stale', false)
        ->assertJsonPath('stores.bazaar.rating', 5)
        ->assertJsonPath('stores.bazaar.rating_count', 21)
        ->assertJsonPath('stores.bazaar.rating_stale', true);
    Cache::flush();
    $page = str_replace('>نصب<', '>changed<', str_replace('<div>۵</div>', '<div>۴٫۹</div>', bazaarPage('۶۰۰')));
    $this->getJson('/admin/installs')
        ->assertJsonPath('stores.bazaar.installs', 600)
        ->assertJsonPath('stores.bazaar.stale', true)
        ->assertJsonPath('stores.bazaar.rating', 4.9)
        ->assertJsonPath('stores.bazaar.rating_stale', false);
});
