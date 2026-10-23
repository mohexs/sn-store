<?php

use App\Models\User;

it('requires login before showing theme preferences', function () {
    $this->get(route('admin.themes.index'))->assertRedirect(route('login'));
});

it('shows theme choices with a browser preference key scoped to the signed in account', function () {
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)->get(route('admin.themes.index'));

    $response->assertOk()
        ->assertViewIs('pages.admin.themes.index')
        ->assertSee('Merah SN Store')
        ->assertSee('Biru Profesional')
        ->assertSee('Hijau Segar')
        ->assertSee('Ungu Modern')
        ->assertSee('Amber Hangat')
        ->assertSee('Slate Minimalis')
        ->assertSee('data-theme-storage-key="sn-store-admin-theme-'.$user->id.'"', escape: false);
});

it('does not apply admin theme preferences to public pages', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk()->assertDontSee('data-admin-theme', escape: false);
});
