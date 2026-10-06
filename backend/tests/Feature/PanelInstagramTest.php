<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires an active admin session for the Instagram panel and its data', function () {
    $this->get('/admin/instagram-queue')->assertRedirect('/admin/login');
    $this->getJson('/admin/instagram-api/instagram-queue')->assertRedirect('/admin/login');
    $this->actingAs(User::factory()->create(['role' => 'user']), 'admin')
        ->postJson('/admin/instagram-api/instagram-templates', [])->assertRedirect('/admin/login');
    $this->actingAs(User::factory()->create(['role' => 'admin', 'active' => false]), 'admin')
        ->get('/admin/instagram-queue')->assertRedirect('/admin/login');
});

it('serves the queue and template operations through the existing panel session', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin', 'active' => true]), 'admin');
    $this->get('/admin/instagram-queue')->assertOk()->assertSee('صف اینستاگرام');
    $data = $this->getJson('/admin/instagram-api/instagram-queue')->assertOk()->assertJsonCount(8, 'catalog')->assertJsonCount(3, 'templates')->json();
    $id = $this->postJson('/admin/instagram-api/instagram-queue', ['verse_key' => '94:5'])->assertCreated()->json('data.id');
    $template = $this->postJson('/admin/instagram-api/instagram-templates', ['name' => 'Panel design', 'design' => $data['templates'][0]['design']])->assertCreated()->json('data');
    $this->putJson("/admin/instagram-api/instagram-queue/$id", ['template_id' => $template['id'], 'status' => 'queued'])->assertOk()->assertJsonPath('data.status', 'queued');
    $this->putJson('/admin/instagram-api/instagram-queue/order', ['ids' => [$id]])->assertOk();
    $this->putJson("/admin/instagram-api/instagram-templates/{$template['id']}", ['name' => 'Updated panel design', 'design' => $data['templates'][0]['design']])->assertOk();
    $this->deleteJson("/admin/instagram-api/instagram-templates/{$template['id']}")->assertNoContent();
    $this->deleteJson("/admin/instagram-api/instagram-queue/$id")->assertNoContent();
});
