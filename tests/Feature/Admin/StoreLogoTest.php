<?php

use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('lets admins replace the combined store identity image on public and admin pages', function () {
    $user = User::factory()->admin()->create();
    Storage::fake('public');
    Storage::disk('public')->put('store/old-logo.png', 'old logo');
    $storeSetting = StoreSetting::query()->create(['logo_path' => 'store/old-logo.png']);

    $response = $this->actingAs($user)->post(route('admin.themes.logo.update'), [
        'logo' => UploadedFile::fake()->image('apotek-aja-lockup.png', 600, 200),
    ]);

    $response->assertRedirect(route('admin.themes.index'))
        ->assertSessionHas('status', 'Gambar identitas toko berhasil diperbarui.');
    $storeSetting->refresh();
    Storage::disk('public')->assertExists($storeSetting->logo_path);
    Storage::disk('public')->assertMissing('store/old-logo.png');

    $logoUrl = Storage::disk('public')->url($storeSetting->logo_path);
    $imageMarkup = 'src="'.$logoUrl.'" alt="Apotek Aja"';
    $this->get(route('home'))->assertOk()->assertSee($imageMarkup, escape: false);
    $this->get(route('dashboard'))->assertOk()->assertSee($imageMarkup, escape: false);
    $this->get(route('admin.themes.index'))->assertOk()->assertSee($logoUrl);
});

it('uses the combined identity image on authentication pages', function () {
    Storage::fake('public');
    Storage::disk('public')->put('store/apotek-aja-lockup.png', 'combined brand image');
    StoreSetting::query()->create(['logo_path' => 'store/apotek-aja-lockup.png']);
    $imageMarkup = 'src="'.Storage::disk('public')->url('store/apotek-aja-lockup.png').'" alt="Apotek Aja"';

    $this->get(route('login'))->assertOk()->assertSee($imageMarkup, escape: false);
    $this->get(route('register'))->assertOk()->assertSee($imageMarkup, escape: false);
});

it('rejects a missing combined identity image without replacing the current file', function () {
    $user = User::factory()->admin()->create();
    Storage::fake('public');
    Storage::disk('public')->put('store/current-logo.png', 'current logo');
    $storeSetting = StoreSetting::query()->create([
        'logo_path' => 'store/current-logo.png',
        'store_name' => 'Apotek Aja',
    ]);

    $this->actingAs($user)->post(route('admin.themes.logo.update'), [])
        ->assertSessionHasErrors('logo');

    expect($storeSetting->fresh()->logo_path)->toBe('store/current-logo.png');
    Storage::disk('public')->assertExists('store/current-logo.png');
});

it('rejects invalid logos without replacing the saved logo', function (string $kind) {
    $user = User::factory()->admin()->create();
    Storage::fake('public');
    Storage::disk('public')->put('store/current-logo.png', 'current logo');
    $storeSetting = StoreSetting::query()->create(['logo_path' => 'store/current-logo.png']);
    $logo = match ($kind) {
        'svg' => UploadedFile::fake()->createWithContent('unsafe.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        'size' => UploadedFile::fake()->image('large.png')->size(2049),
        'dimensions' => UploadedFile::fake()->image('wide.png', 4097, 1),
    };

    $response = $this->actingAs($user)->post(route('admin.themes.logo.update'), ['logo' => $logo]);

    $response->assertSessionHasErrors('logo');
    $storeSetting->refresh();
    expect($storeSetting->logo_path)->toBe('store/current-logo.png');
    Storage::disk('public')->assertExists('store/current-logo.png');
    expect(Storage::disk('public')->allFiles())->toBe(['store/current-logo.png']);
})->with(['svg', 'size', 'dimensions']);

it('requires an admin to update the store logo', function () {
    Storage::fake('public');
    $customer = User::factory()->create();

    $response = $this->actingAs($customer)->post(route('admin.themes.logo.update'), [
        'logo' => UploadedFile::fake()->image('customer-logo.png'),
    ]);

    $response->assertForbidden();
    $this->assertDatabaseCount('store_settings', 0);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('requires authentication to update the store logo', function () {
    $response = $this->post(route('admin.themes.logo.update'), [
        'logo' => UploadedFile::fake()->image('guest-logo.png'),
    ]);

    $response->assertRedirect(route('login'));
});
