<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('redirects guests before allowing access to product management', function (string $method, string $path) {
    $this->call($method, $path)->assertRedirect(route('login'));
})->with([
    'list' => ['GET', '/admin/products'],
    'create form' => ['GET', '/admin/products/create'],
    'store' => ['POST', '/admin/products'],
    'edit form' => ['GET', '/admin/products/1/edit'],
    'update' => ['PUT', '/admin/products/1'],
]);

it('renders the product form with the available categories', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.products.create'));

    $response->assertOk()
        ->assertViewIs('pages.admin.products.create')
        ->assertSee('SKU')
        ->assertSee('Nama Principal')
        ->assertViewHas('categories', fn ($categories) => $categories->contains('name', 'Alat Kesehatan'));
});

it('lists saved products and escapes their names', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['name' => '<script>alert("unsafe")</script>']);

    $response = $this->actingAs($user)->get(route('admin.products.index'));

    $response->assertOk()
        ->assertSee($product->name)
        ->assertDontSee($product->name, escape: false)
        ->assertSee($product->sku);
});

it('renders saved product values in the edit form', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.products.edit', $product));

    $response->assertOk()
        ->assertViewIs('pages.admin.products.edit')
        ->assertSee($product->sku)
        ->assertSee($product->principal_name);
});

it('stores product details and a photo while preserving barcode leading zeros', function () {
    $user = User::factory()->create();
    Storage::fake('public');
    $data = Product::factory()->make(['sku' => 'sn-vit-001', 'barcode' => '0001234567890'])->getAttributes();
    $photo = UploadedFile::fake()->image('vitamin.png', 100, 100);

    $response = $this->actingAs($user)->post(route('admin.products.store'), [
        ...$data,
        'photo' => $photo,
        'photo_path' => 'untrusted-path.png',
    ]);

    $response->assertRedirect(route('admin.products.index'))->assertSessionHas('status', 'Produk berhasil ditambahkan.');
    $this->assertDatabaseHas('products', [
        'sku' => 'SN-VIT-001', 'name' => $data['name'], 'barcode' => '0001234567890',
        'description' => $data['description'], 'category_id' => $data['category_id'],
        'weight' => 250, 'length' => 12, 'width' => 8, 'height' => 6, 'price' => 45000,
        'rack_location' => 'A-01', 'principal_name' => 'PT Sehat Bersama',
    ]);
    $this->assertDatabaseCount('products', 1);
    $product = Product::query()->sole();
    expect($product->photo_path)->toStartWith('products/')->not->toBe('untrusted-path.png');
    Storage::disk('public')->assertExists($product->photo_path);
});

it('rejects incomplete product data without creating a record', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->from(route('admin.products.create'))->post(route('admin.products.store'), []);

    $response->assertRedirect(route('admin.products.create'))
        ->assertSessionHasErrors(['sku', 'name', 'category_id', 'weight', 'length', 'width', 'height', 'price', 'principal_name']);
    $this->assertDatabaseCount('products', 0);
});

it('rejects duplicate identifiers without creating another product', function () {
    $user = User::factory()->create();
    $existing = Product::factory()->create(['sku' => 'SN-001', 'barcode' => '000123']);
    $data = Product::factory()->make(['sku' => 'sn-001', 'barcode' => $existing->barcode])->getAttributes();

    $response = $this->actingAs($user)->post(route('admin.products.store'), $data);

    $response->assertSessionHasErrors([
        'sku' => 'SKU sudah digunakan oleh produk lain.',
        'barcode' => 'Barcode sudah digunakan oleh produk lain.',
    ]);
    $this->assertDatabaseCount('products', 1);
});

it('rejects invalid amounts and categories', function (array $invalid, string $field) {
    $user = User::factory()->create();
    $data = Product::factory()->make()->getAttributes();

    $response = $this->actingAs($user)->post(route('admin.products.store'), array_replace($data, $invalid));

    $response->assertSessionHasErrors($field);
    $this->assertDatabaseCount('products', 0);
})->with([
    'negative price' => [['price' => -1], 'price'],
    'zero weight' => [['weight' => 0], 'weight'],
    'negative length' => [['length' => -1], 'length'],
    'negative width' => [['width' => -1], 'width'],
    'negative height' => [['height' => -1], 'height'],
    'unknown category' => [['category_id' => 999999], 'category_id'],
    'too many decimals' => [['price' => '12.345'], 'price'],
]);

it('rejects unsupported or oversized photos', function (string $kind) {
    $user = User::factory()->create();
    Storage::fake('public');
    $data = Product::factory()->make()->getAttributes();
    $photo = match ($kind) {
        'svg' => UploadedFile::fake()->createWithContent('unsafe.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        'size' => UploadedFile::fake()->image('large.png')->size(2049),
        'dimensions' => UploadedFile::fake()->image('wide.png', 4097, 1),
    };

    $response = $this->actingAs($user)->post(route('admin.products.store'), [...$data, 'photo' => $photo]);

    $response->assertSessionHasErrors('photo');
    $this->assertDatabaseCount('products', 0);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
})->with(['svg', 'size', 'dimensions']);

it('updates a product with its own identifiers and keeps the existing photo', function () {
    $user = User::factory()->create();
    Storage::fake('public');
    Storage::disk('public')->put('products/existing.png', 'photo');
    $product = Product::factory()->create(['photo_path' => 'products/existing.png']);
    $data = $product->only(['sku', 'name', 'description', 'category_id', 'weight', 'length', 'width', 'height', 'price', 'barcode', 'rack_location', 'principal_name']);

    $response = $this->actingAs($user)->put(route('admin.products.update', $product), [...$data, 'name' => 'Produk diperbarui']);

    $response->assertRedirect(route('admin.products.index'))->assertSessionHas('status', 'Produk berhasil diperbarui.');
    $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Produk diperbarui', 'photo_path' => 'products/existing.png']);
    Storage::disk('public')->assertExists('products/existing.png');
});

it('replaces an old photo after successfully saving a new one', function () {
    $user = User::factory()->create();
    Storage::fake('public');
    Storage::disk('public')->put('products/old.png', 'old photo');
    $product = Product::factory()->create(['photo_path' => 'products/old.png']);
    $data = $product->only(['sku', 'name', 'description', 'category_id', 'weight', 'length', 'width', 'height', 'price', 'barcode', 'rack_location', 'principal_name']);

    $response = $this->actingAs($user)->put(route('admin.products.update', $product), [
        ...$data, 'photo' => UploadedFile::fake()->image('replacement.png'),
    ]);

    $response->assertRedirect(route('admin.products.index'))->assertSessionHasNoErrors();
    $product->refresh();
    Storage::disk('public')->assertExists($product->photo_path);
    Storage::disk('public')->assertMissing('products/old.png');
});

it('stores a product when optional fields and photo are empty', function () {
    $user = User::factory()->create();
    $data = Product::factory()->make(['description' => null, 'barcode' => null, 'rack_location' => null])->getAttributes();

    $response = $this->actingAs($user)->post(route('admin.products.store'), $data);

    $response->assertRedirect(route('admin.products.index'))->assertSessionHasNoErrors();
    $this->assertDatabaseHas('products', ['sku' => $data['sku'], 'barcode' => null, 'photo_path' => null]);
});

it('rejects conflicting identifiers on update and keeps the existing photo', function () {
    $user = User::factory()->create();
    Storage::fake('public');
    Storage::disk('public')->put('products/kept.png', 'existing photo');
    $other = Product::factory()->create();
    $product = Product::factory()->create(['photo_path' => 'products/kept.png']);
    $data = $product->only(['sku', 'name', 'description', 'category_id', 'weight', 'length', 'width', 'height', 'price', 'barcode', 'rack_location', 'principal_name']);

    $response = $this->actingAs($user)->put(route('admin.products.update', $product), [
        ...$data, 'sku' => $other->sku, 'barcode' => $other->barcode,
        'photo' => UploadedFile::fake()->image('not-saved.png'),
    ]);

    $response->assertSessionHasErrors(['sku', 'barcode']);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'sku' => $product->sku, 'photo_path' => 'products/kept.png']);
    expect(Storage::disk('public')->allFiles())->toBe(['products/kept.png']);
});
