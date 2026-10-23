<?php

use App\Models\User;

it('requires login for customer management', function (string $method, string $path) {
    $this->call($method, $path)->assertRedirect(route('login'));
})->with([['GET', '/admin/users'], ['GET', '/admin/users/1/edit'], ['PATCH', '/admin/users/1']]);

it('rejects customer access to other customer data', function (string $method, string $path) {
    $customer = User::factory()->create();

    $this->actingAs($customer)->call($method, $path)->assertForbidden();
})->with([['GET', '/admin/users'], ['GET', '/admin/users/1/edit'], ['PATCH', '/admin/users/1']]);

it('lists customer profiles and excludes admin accounts', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create(['name' => '<script>customer</script>', 'phone' => '08123456789', 'address' => '<script>address</script>']);

    $response = $this->actingAs($admin)->get(route('admin.users.index'));

    $response->assertOk()->assertSee($customer->email)->assertSee($customer->phone)
        ->assertSee($customer->name)->assertDontSee($customer->name, escape: false)
        ->assertSee($customer->address)->assertDontSee($customer->address, escape: false)
        ->assertViewHas('users', fn ($users) => $users->total() === 1);
});

it('searches customers by their profile information', function (string $field, string $value) {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create([$field => $value]);
    $other = User::factory()->create();

    $response = $this->actingAs($admin)->get(route('admin.users.index', ['search' => $value]));

    $response->assertOk()->assertSee($customer->email)->assertDontSee($other->email)
        ->assertViewHas('users', fn ($users) => $users->total() === 1);
})->with([['name', 'Pelanggan Khusus'], ['email', 'customer@example.test'], ['phone', '081299998888'], ['city', 'Makassar']]);

it('shows an empty result for an unmatched search', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'Tidak ditemukan']))
        ->assertOk()->assertSee('Tidak ada customer yang cocok dengan pencarian.');
});

it('paginates customers and preserves the search', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->count(11)->create(['city' => 'Makassar']);

    $this->actingAs($admin)->get(route('admin.users.index', ['search' => 'Makassar', 'page' => 2]))
        ->assertOk()->assertViewHas('users', fn ($users) => $users->count() === 1 && $users->total() === 11)
        ->assertSee('search=Makassar');
});

it('shows the saved customer profile', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create(['address' => '<script>unsafe</script>', 'city' => 'Makassar']);

    $this->actingAs($admin)->get(route('admin.users.edit', $customer))->assertOk()
        ->assertSee($customer->email)->assertSee($customer->address)->assertDontSee($customer->address, escape: false)->assertSee('Makassar');
});

it('updates customer details without changing privileged fields', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();
    $password = $customer->password;

    $response = $this->actingAs($admin)->patch(route('admin.users.update', $customer), [
        'name' => 'Nama Baru', 'email' => 'baru@example.test', 'phone' => '+62 812-3456',
        'address' => 'Jl. Pelanggan No. 10', 'city' => 'Makassar', 'province' => 'Sulawesi Selatan',
        'postal_code' => '90111', 'is_admin' => true, 'password' => 'changed', 'email_verified_at' => now(),
    ]);

    $response->assertRedirect(route('admin.users.edit', $customer))->assertSessionHasNoErrors();
    $this->assertDatabaseHas('users', ['id' => $customer->id, 'name' => 'Nama Baru', 'email' => 'baru@example.test',
        'phone' => '+62 812-3456', 'address' => 'Jl. Pelanggan No. 10', 'city' => 'Makassar',
        'province' => 'Sulawesi Selatan', 'postal_code' => '90111', 'is_admin' => false,
        'password' => $password, 'email_verified_at' => null]);
});

it('preserves email verification when saving the same email', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();

    $this->actingAs($admin)->patch(route('admin.users.update', $customer), ['name' => 'Nama Baru', 'email' => $customer->email])
        ->assertRedirect()->assertSessionHasNoErrors();

    expect($customer->fresh()->email_verified_at)->not->toBeNull();
});

it('rejects invalid customer details without saving', function (array $invalid, string $field) {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();

    $this->actingAs($admin)->patch(route('admin.users.update', $customer), array_replace([
        'name' => 'Nama Baru', 'email' => $customer->email,
    ], $invalid))->assertSessionHasErrors($field);

    $this->assertDatabaseHas('users', ['id' => $customer->id, 'name' => $customer->name, 'email' => $customer->email]);
})->with([
    'blank name' => [['name' => ' '], 'name'],
    'long name' => [['name' => str_repeat('a', 256)], 'name'],
    'invalid email' => [['email' => 'invalid'], 'email'],
    'invalid phone' => [['phone' => 'not-a-number'], 'phone'],
    'long address' => [['address' => str_repeat('a', 2001)], 'address'],
    'long city' => [['city' => str_repeat('a', 101)], 'city'],
    'long province' => [['province' => str_repeat('a', 101)], 'province'],
    'invalid postal code' => [['postal_code' => 'abcde'], 'postal_code'],
]);

it('rejects an email already used by another account', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create();

    $this->actingAs($admin)->patch(route('admin.users.update', $customer), ['name' => $customer->name, 'email' => $admin->email])
        ->assertSessionHasErrors(['email' => 'Email sudah digunakan akun lain.']);

    $this->assertDatabaseHas('users', ['id' => $customer->id, 'email' => $customer->email]);
});

it('does not expose admin profiles through customer management', function (string $method, string $suffix) {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->call($method, '/admin/users/'.$admin->id.$suffix, ['name' => 'Name', 'email' => $admin->email])->assertNotFound();
})->with([['GET', '/edit'], ['PATCH', '']]);

it('returns not found for a missing customer', function (string $method, string $suffix) {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->call($method, '/admin/users/999999'.$suffix, ['name' => 'Name', 'email' => 'missing@example.test'])->assertNotFound();
})->with([['GET', '/edit'], ['PATCH', '']]);

it('allows a customer to complete their own shipping profile without elevating privileges', function () {
    $customer = User::factory()->create();

    $this->actingAs($customer)->patch(route('profile.update'), [
        'name' => $customer->name, 'email' => $customer->email, 'phone' => '08123456789',
        'address' => 'Jalan Pelanggan 10', 'city' => 'Makassar', 'province' => 'Sulawesi Selatan',
        'postal_code' => '90111', 'is_admin' => true,
    ])->assertRedirect(route('profile.edit'))->assertSessionHasNoErrors();

    $this->assertDatabaseHas('users', ['id' => $customer->id, 'address' => 'Jalan Pelanggan 10', 'city' => 'Makassar', 'phone' => '08123456789', 'is_admin' => false]);
    $this->actingAs($customer)->get(route('profile.edit'))->assertOk()->assertSee('Jalan Pelanggan 10');
});
