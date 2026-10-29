<?php

use App\Models\HeroAd;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('lets admins create hero banners and displays active banner content on the public page', function () {
    $user = User::factory()->admin()->create();
    Storage::fake('public');

    $response = $this->actingAs($user)->post(route('admin.ads.store'), [
        'eyebrow' => 'PROMO SEHAT',
        'title' => 'Belanja lebih',
        'accent' => 'hemat hari ini.',
        'copy' => 'Temukan penawaran terbaik untuk keluarga Anda.',
        'image' => UploadedFile::fake()->image('hero.webp', 1200, 900),
        'sort_order' => 1,
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.ads.index'))
        ->assertSessionHas('status', 'Banner hero berhasil ditambahkan.');
    $ad = HeroAd::query()->sole();
    Storage::disk('public')->assertExists($ad->image_path);
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('PROMO SEHAT')
        ->assertSee('Belanja lebih')
        ->assertSee(Storage::disk('public')->url($ad->image_path));
});

it('updates banner copy and replaces only the image selected for replacement', function () {
    $user = User::factory()->admin()->create();
    Storage::fake('public');
    Storage::disk('public')->put('hero-ads/old.png', 'old image');
    $ad = HeroAd::query()->create([
        'eyebrow' => 'LABEL',
        'title' => 'Judul lama',
        'accent' => 'aksen lama',
        'copy' => 'Keterangan lama',
        'image_path' => 'hero-ads/old.png',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $this->actingAs($user)->put(route('admin.ads.update', $ad), [
        'eyebrow' => 'LABEL BARU',
        'title' => 'Judul baru',
        'accent' => 'aksen baru',
        'copy' => 'Keterangan baru',
        'image' => UploadedFile::fake()->image('replacement.png', 1200, 900),
        'sort_order' => 2,
        'is_active' => 1,
    ])->assertRedirect(route('admin.ads.index'));

    $ad->refresh();
    expect($ad->title)->toBe('Judul baru')->and($ad->image_path)->not->toBe('hero-ads/old.png');
    Storage::disk('public')->assertExists($ad->image_path);
    Storage::disk('public')->assertMissing('hero-ads/old.png');
});

it('shows the accepted file limits and recommended banner resolution on the Ads form', function () {
    $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.ads.create'));

    $response->assertOk()
        ->assertSee('1200 × 900 px')
        ->assertSee('Maks. 2 MB')
        ->assertSee('JPG, JPEG, PNG, WebP');
});

it('uses the interactive delete confirmation for hero banners', function () {
    $user = User::factory()->admin()->create();
    HeroAd::query()->create([
        'eyebrow' => 'PROMO',
        'title' => 'Banner pilihan',
        'accent' => 'hemat',
        'copy' => 'Informasi banner.',
        'image_path' => 'hero-ads/banner.png',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)->get(route('admin.ads.index'));

    $response->assertOk()
        ->assertSee('data-confirm-delete', escape: false)
        ->assertSee('data-confirm-title="Hapus banner ini?"', escape: false)
        ->assertDontSee('onsubmit="return confirm(', escape: false);
});

it('hides all hero ads when the saved banners are inactive', function () {
    Storage::fake('public');
    HeroAd::query()->create([
        'eyebrow' => 'NONAKTIF',
        'title' => 'Banner tersembunyi',
        'accent' => 'tidak tampil',
        'copy' => 'Banner ini sedang tidak aktif.',
        'image_path' => 'hero-ads/inactive.png',
        'sort_order' => 0,
        'is_active' => false,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Banner tersembunyi')
        ->assertDontSee('LANGKAH KECIL, HIDUP LEBIH SEHAT');
});

it('rejects unsupported oversized and excessive-dimension banner images', function (string $kind) {
    $user = User::factory()->admin()->create();
    Storage::fake('public');
    $image = match ($kind) {
        'svg' => UploadedFile::fake()->createWithContent('unsafe.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        'size' => UploadedFile::fake()->image('large.png')->size(2049),
        'dimensions' => UploadedFile::fake()->image('wide.png', 4097, 1),
    };

    $response = $this->actingAs($user)->from(route('admin.ads.create'))->post(route('admin.ads.store'), [
        'eyebrow' => 'LABEL',
        'title' => 'Judul',
        'accent' => 'Aksen',
        'copy' => 'Keterangan',
        'image' => $image,
        'sort_order' => 0,
        'is_active' => 1,
    ]);

    $response->assertRedirect(route('admin.ads.create'))
        ->assertSessionHasErrors('image');
    $this->assertDatabaseCount('hero_ads', 0);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
})->with(['svg', 'size', 'dimensions']);

it('requires admin authorization for Ads management', function () {
    $customer = User::factory()->create();

    $this->get(route('admin.ads.index'))->assertRedirect(route('login'));
    $this->actingAs($customer)->get(route('admin.ads.index'))->assertForbidden();
});
