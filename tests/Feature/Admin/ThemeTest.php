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
        ->assertSee('Merah Apotek Aja')
        ->assertSee('Biru Profesional')
        ->assertSee('Hijau Segar')
        ->assertSee('Ungu Modern')
        ->assertSee('Amber Hangat')
        ->assertSee('Slate Minimalis')
        ->assertSee(route('admin.themes.logo.update'))
        ->assertSee('name="logo"', escape: false)
        ->assertDontSee('name="store_name"', escape: false)
        ->assertSee('Gambar logo dan nama toko')
        ->assertSee('satu gambar sudah memuat logo beserta tulisan nama toko')
        ->assertSee('data-theme-storage-key="sn-store-admin-theme-'.$user->id.'"', escape: false)
        ->assertSee('data-mode-storage-key="sn-store-admin-mode-'.$user->id.'"', escape: false)
        ->assertSee('Aktifkan mode gelap')
        ->assertSee(':aria-pressed="darkMode"', escape: false)
        ->assertSee('@click="toggleMode()"', escape: false);
});

it('does not apply admin theme preferences to public pages', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertOk()
        ->assertDontSee('data-admin-theme', escape: false)
        ->assertSee('Apotek Aja')
        ->assertDontSee('SN Store');
});

it('shows Apotek Aja branding on login and registration pages', function () {
    $this->get(route('login'))->assertOk()->assertSee('Masuk — Apotek Aja')->assertDontSee('SN Store');
    $this->get(route('register'))->assertOk()->assertSee('Daftar — Apotek Aja')->assertDontSee('SN Store');
});
