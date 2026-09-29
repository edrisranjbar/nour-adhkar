<?php

use App\Models\User;
use App\Support\Analytics\Visitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

const PHONE_UA = 'Mozilla/5.0 (Linux; Android 14; 23129RAA4G) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0 Mobile Safari/537.36';

function panelAdmin(): User
{
    return User::factory()->create(['role' => 'admin', 'active' => true]);
}

it('records visits anonymously with device, referrer and campaign details', function () {
    $this->withHeader('User-Agent', PHONE_UA)->postJson('/api/analytics/visit', [
        'path' => '/',
        'referrer' => 'https://t.me/some_channel',
        'ua' => PHONE_UA,
        'session_id' => 'abc-123',
        'lang' => 'fa-IR',
        'utm_source' => 'telegram',
        'utm_campaign' => 'launch',
    ])->assertOk();

    $visit = DB::table('page_visits')->first();
    expect($visit->ip)->toBeNull()
        ->and($visit->visitor_hash)->toHaveLength(64)
        ->and($visit->device)->toBe('mobile')
        ->and($visit->os)->toBe('Android')
        ->and($visit->browser)->toBe('Chrome')
        ->and($visit->referrer_host)->toBe('t.me')
        ->and($visit->lang)->toBe('fa-ir')
        ->and($visit->utm_campaign)->toBe('launch');
});

it('ignores bots and only accepts known landing events', function () {
    $this->postJson('/api/analytics/visit', ['path' => '/', 'ua' => 'Googlebot/2.1 (+http://www.google.com/bot.html)'])->assertOk();
    expect(DB::table('page_visits')->count())->toBe(0);

    $this->withHeader('User-Agent', PHONE_UA)->postJson('/api/analytics/event', ['name' => 'store_click', 'label' => 'hero'])->assertOk();
    $this->withHeader('User-Agent', PHONE_UA)->postJson('/api/analytics/event', ['name' => 'anything'])->assertUnprocessable();
    expect(DB::table('analytics_events')->count())->toBe(1);
});

it('tells browsers apart that all mention Chrome', function () {
    expect(Visitor::parse('Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 Chrome/129.0 Safari/537.36 Edg/129.0')['browser'])->toBe('Edge')
        ->and(Visitor::parse('Mozilla/5.0 (Linux; Android 13) Chrome/129.0 Mobile Safari/537.36 OPR/80.0')['browser'])->toBe('Opera')
        ->and(Visitor::parse('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) Version/17.0 Mobile/15E148 Safari/604.1'))->toMatchArray(['browser' => 'Safari', 'os' => 'iOS', 'device' => 'mobile']);
});

it('requires an admin to see analytics', function () {
    $this->get('/admin/analytics')->assertRedirect('/admin/login');
});

it('renders website and app analytics for an admin', function () {
    foreach (['hero', 'footer'] as $label) {
        $this->withHeader('User-Agent', PHONE_UA)->postJson('/api/analytics/visit', ['path' => '/', 'ua' => PHONE_UA, 'referrer' => 'https://t.me/x']);
        $this->withHeader('User-Agent', PHONE_UA)->postJson('/api/analytics/event', ['name' => 'store_click', 'label' => $label]);
    }
    // A visit recorded before this change (IP only, no hash).
    DB::table('page_visits')->insert(['path' => '/privacy', 'ip' => '10.0.0.1', 'visited_at' => now()->subDays(3)]);

    $admin = panelAdmin();

    foreach ([1, 7, 30, 90] as $range) {
        $this->actingAs($admin, 'admin')->get("/admin/analytics?range=$range")
            ->assertOk()
            ->assertSee('آمار و گزارش‌ها')
            ->assertSee('کلیک دریافت از کافه‌بازار')
            ->assertSee('t.me');
    }

    $this->actingAs($admin, 'admin')->get('/admin/analytics?tab=app&range=7')
        ->assertOk()
        ->assertSee('ثبت‌نام و بازخورد');
});
