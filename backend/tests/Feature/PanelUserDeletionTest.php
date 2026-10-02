<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('shows a confirmed delete action only for inactive users', function () {
    $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
    $active = User::factory()->create(['active' => true]);
    $inactive = User::factory()->create(['active' => false]);

    $this->actingAs($admin, 'admin')->get('/admin/users')
        ->assertOk()
        ->assertSee('action="'.route('panel.users.destroy', $inactive->id).'"', false)
        ->assertDontSee('action="'.route('panel.users.destroy', $active->id).'"', false)
        ->assertDontSee('action="'.route('panel.users.destroy', $admin->id).'"', false)
        ->assertSee("onsubmit=\"return confirm('آیا از حذف این حساب کاربری", false)
        ->assertSee('name="_method" value="DELETE"', false);
});

it('deletes an inactive account and its progress backup and returns to the filtered page', function () {
    $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
    $inactive = User::factory()->create(['active' => false]);
    DB::table('app_progress_backups')->insert([
        'user_id' => $inactive->id,
        'records' => '[]',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs($admin, 'admin')->from('/admin/users?q=test&page=2')
        ->delete(route('panel.users.destroy', $inactive->id))
        ->assertRedirect('/admin/users?q=test&page=2')
        ->assertSessionHas('status', 'حساب کاربر حذف شد.');

    $this->assertDatabaseMissing('users', ['id' => $inactive->id]);
    $this->assertDatabaseMissing('app_progress_backups', ['user_id' => $inactive->id]);
});

it('rejects deleting an active or reactivated user', function () {
    $admin = User::factory()->create(['role' => 'admin', 'active' => true]);
    $user = User::factory()->create(['active' => false]);
    $user->update(['active' => true]);

    $this->actingAs($admin, 'admin')->from('/admin/users')
        ->delete(route('panel.users.destroy', $user->id))
        ->assertRedirect('/admin/users')
        ->assertSessionHas('status', 'فقط حساب‌های غیرفعال قابل حذف هستند.');

    $this->assertDatabaseHas('users', ['id' => $user->id, 'active' => true]);
});

it('does not delete the signed-in administrator', function () {
    $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

    $this->actingAs($admin, 'admin')->from('/admin/users')
        ->delete(route('panel.users.destroy', $admin->id))
        ->assertRedirect('/admin/users')
        ->assertSessionHas('status', 'نمی‌توانید حساب خودتان را حذف کنید.');

    $this->assertDatabaseHas('users', ['id' => $admin->id]);
});

it('requires an active administrator to delete users', function ($attributes) {
    $inactive = User::factory()->create(['active' => false]);
    if ($attributes !== null) {
        $this->actingAs(User::factory()->create($attributes), 'admin');
    }

    $this->delete(route('panel.users.destroy', $inactive->id))->assertRedirect('/admin/login');
    $this->assertDatabaseHas('users', ['id' => $inactive->id]);
})->with([
    'guest' => [null],
    'regular user' => [['role' => 'user', 'active' => true]],
    'inactive admin' => [['role' => 'admin', 'active' => false]],
]);

it('returns not found for an unknown account', function () {
    $admin = User::factory()->create(['role' => 'admin', 'active' => true]);

    $this->actingAs($admin, 'admin')->delete(route('panel.users.destroy', 999999))->assertNotFound();
});
