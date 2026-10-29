<?php

use App\Models\Promotion;
use App\Models\User;

it('requires admin access to manage promotions', function () {
    $this->get(route('admin.promotions.index'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())
        ->get(route('admin.promotions.index'))
        ->assertForbidden();
});

it('uses Indonesian date formatting for promotion period inputs', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)->get(route('admin.promotions.create'))->assertOk()
        ->assertSee('data-date-type="datetime-local"', escape: false)
        ->assertSee('placeholder="dd/mm/yyyy --:--"', escape: false)
        ->assertSee('Format tanggal: dd/mm/yyyy (waktu 24 jam)')
        ->assertSee('name="starts_at" type="text"', escape: false);
});

it('shows upcoming running and completed programs based on their dates', function () {
    $this->travelTo('2026-03-01 12:00:00');
    $user = User::factory()->admin()->create();
    $upcoming = Promotion::query()->create([
        'name' => 'Promo mendatang',
        'discount_type' => 'fixed',
        'discount_value' => 10000,
        'starts_at' => now()->addDay(),
        'ends_at' => now()->addDays(2),
    ]);
    $running = Promotion::query()->create([
        'name' => 'Promo berjalan',
        'discount_type' => 'percentage',
        'discount_value' => 15,
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
    ]);
    $completed = Promotion::query()->create([
        'name' => 'Promo selesai',
        'discount_type' => 'fixed',
        'discount_value' => 5000,
        'starts_at' => now()->subDays(2),
        'ends_at' => now()->subSecond(),
    ]);

    $response = $this->actingAs($user)->get(route('admin.promotions.index'));

    $response->assertOk()
        ->assertSee('Akan datang')
        ->assertSee('Berjalan')
        ->assertSee('Selesai')
        ->assertSee('Rp10.000,00')
        ->assertSee('15,00%')
        ->assertSee('02/03/2026')
        ->assertSee('28/02/2026')
        ->assertSee('data-confirm-delete', escape: false)
        ->assertSee('data-confirm-title="Hapus program diskon ini?"', escape: false)
        ->assertDontSee('onsubmit="return confirm(', escape: false);
    expect($upcoming->status)->toBe('upcoming')
        ->and($running->status)->toBe('running')
        ->and($completed->status)->toBe('completed');
});

it('creates a percentage discount scheduled for a future period', function () {
    $this->travelTo('2026-03-01 12:00:00');
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)->post(route('admin.promotions.store'), [
        'name' => 'Diskon akhir pekan',
        'description' => 'Promo yang dimulai minggu depan.',
        'discount_type' => 'percentage',
        'discount_value' => 15,
        'starts_at' => '2026-03-08T09:00',
        'ends_at' => '2026-03-10T21:00',
    ]);

    $response->assertRedirect(route('admin.promotions.index'))
        ->assertSessionHas('status', 'Program diskon berhasil ditambahkan.');
    $this->assertDatabaseHas('promotions', [
        'name' => 'Diskon akhir pekan',
        'discount_type' => 'percentage',
        'discount_value' => 15,
        'starts_at' => '2026-03-08 09:00:00',
        'ends_at' => '2026-03-10 21:00:00',
    ]);
    expect(Promotion::query()->sole()->status)->toBe('upcoming');
});

it('rejects invalid discount values and periods', function (array $invalid, string $field) {
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)->from(route('admin.promotions.create'))->post(route('admin.promotions.store'), [
        'name' => 'Promo tidak valid',
        'discount_type' => 'percentage',
        'discount_value' => 10,
        'starts_at' => '2026-03-08T09:00',
        'ends_at' => '2026-03-10T21:00',
        ...$invalid,
    ]);

    $response->assertRedirect(route('admin.promotions.create'))
        ->assertSessionHasErrors($field);
    $this->assertDatabaseCount('promotions', 0);
})->with([
    'percentage over 100' => [['discount_value' => 100.01], 'discount_value'],
    'zero discount' => [['discount_value' => 0], 'discount_value'],
    'end before start' => [['ends_at' => '2026-03-08T08:59'], 'ends_at'],
]);

it('updates and deletes an existing discount program', function () {
    $user = User::factory()->admin()->create();
    $promotion = Promotion::query()->create([
        'name' => 'Promo lama',
        'discount_type' => 'fixed',
        'discount_value' => 5000,
        'starts_at' => '2026-03-08 09:00:00',
        'ends_at' => '2026-03-10 21:00:00',
    ]);

    $this->actingAs($user)->put(route('admin.promotions.update', $promotion), [
        'name' => 'Promo baru',
        'discount_type' => 'fixed',
        'discount_value' => 12000,
        'starts_at' => '2026-03-09T10:00',
        'ends_at' => '2026-03-11T20:00',
    ])->assertRedirect(route('admin.promotions.index'));

    $this->assertDatabaseHas('promotions', [
        'id' => $promotion->id,
        'name' => 'Promo baru',
        'discount_value' => 12000,
        'starts_at' => '2026-03-09 10:00:00',
    ]);

    $this->delete(route('admin.promotions.destroy', $promotion))
        ->assertRedirect(route('admin.promotions.index'));
    $this->assertDatabaseCount('promotions', 0);
});
