<?php

use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

it('redirects guests before allowing access to product management', function (string $method, string $path) {
    $this->call($method, $path)->assertRedirect(route('login'));
})->with([
    'list' => ['GET', '/admin/products'],
    'create form' => ['GET', '/admin/products/create'],
    'store' => ['POST', '/admin/products'],
    'edit form' => ['GET', '/admin/products/1/edit'],
    'update' => ['PUT', '/admin/products/1'],
    'delete' => ['DELETE', '/admin/products/1'],
    'photo' => ['GET', '/admin/products/1/photo'],
    'export' => ['GET', '/admin/products/export'],
]);

it('renders the product form with the available categories', function () {
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)->get(route('admin.products.create'));

    $response->assertOk()
        ->assertViewIs('pages.admin.products.create')
        ->assertSee('SKU')
        ->assertSee('Variasi produk')
        ->assertSee('Nama Principal')
        ->assertViewHas('categories', fn ($categories) => $categories->contains('name', 'Alat Kesehatan'));
});

it('lists saved products and escapes their names', function () {
    $user = User::factory()->admin()->create();
    $product = Product::factory()->create(['name' => '<script>alert("unsafe")</script>']);

    $response = $this->actingAs($user)->get(route('admin.products.index'));

    $response->assertOk()
        ->assertSee($product->name)
        ->assertDontSee($product->name, escape: false)
        ->assertSee($product->sku)
        ->assertSee('Export CSV')
        ->assertSee(route('admin.products.export'))
        ->assertSee(route('admin.products.destroy', $product))
        ->assertSee('data-confirm-delete', escape: false);
});

it('exports every product and its category and variations as an Excel-compatible CSV', function () {
    $user = User::factory()->admin()->create();
    $products = Product::factory()->count(11)->create();
    $products[0]->update(['name' => '=2+2']);
    $products[0]->variations()->create([
        'name' => 'Isi 30 tablet',
        'sku' => 'SN-EXPORT-30',
        'price' => 32000,
        'weight' => 125,
    ]);
    $products[0]->variations()->create([
        'name' => 'Isi 60 tablet',
        'sku' => 'SN-EXPORT-60',
        'price' => 55000,
        'weight' => 250,
    ]);

    $response = $this->actingAs($user)->get(route('admin.products.export'));

    $response->assertOk()
        ->assertDownload('products.csv')
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $csv = $response->streamedContent();
    expect($csv)->toStartWith("\xEF\xBB\xBF");

    $lines = array_values(array_filter(explode("\r\n", $csv)));
    $headers = str_getcsv(mb_substr(array_shift($lines), 1));
    $rows = array_map(fn (string $line): array => str_getcsv($line), $lines);
    $skuIndex = array_search('sku', $headers, true);
    $nameIndex = array_search('name', $headers, true);
    $categoryIndex = array_search('category_name', $headers, true);
    $variationSkuIndex = array_search('variation_sku', $headers, true);

    expect($rows)->toHaveCount(12)
        ->and(array_column($rows, $skuIndex))->toContain(...$products->pluck('sku')->all())
        ->and($rows[0][$nameIndex])->toBe("'=2+2")
        ->and($rows[0][$categoryIndex])->toBe($products[0]->category->name)
        ->and($rows[0][$variationSkuIndex])->toBe('SN-EXPORT-30')
        ->and($rows[1][$variationSkuIndex])->toBe('SN-EXPORT-60')
        ->and($headers)->toContain('photo_path', 'category_slug', 'variation_weight');
});

it('renders saved product values in the edit form', function () {
    $user = User::factory()->admin()->create();
    $product = Product::factory()->create();
    $variation = $product->variations()->create([
        'name' => 'Isi 30 tablet',
        'sku' => 'SN-VIT-30',
        'price' => 32000,
        'weight' => 125,
    ]);

    $response = $this->actingAs($user)->get(route('admin.products.edit', $product));

    $response->assertOk()
        ->assertViewIs('pages.admin.products.edit')
        ->assertSee($product->sku)
        ->assertSee($variation->sku)
        ->assertSee($product->principal_name);
});

it('stores product details and a photo while preserving barcode leading zeros', function () {
    $user = User::factory()->admin()->create();
    Storage::fake('local');
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
    Storage::disk('local')->assertExists($product->photo_path);
});

it('rejects incomplete product data without creating a record', function () {
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)->from(route('admin.products.create'))->post(route('admin.products.store'), []);

    $response->assertRedirect(route('admin.products.create'))
        ->assertSessionHasErrors(['sku', 'name', 'category_id', 'weight', 'length', 'width', 'height', 'price', 'principal_name']);
    $this->assertDatabaseCount('products', 0);
});

it('rejects duplicate identifiers without creating another product', function () {
    $user = User::factory()->admin()->create();
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
    $user = User::factory()->admin()->create();
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
    $user = User::factory()->admin()->create();
    Storage::fake('local');
    $data = Product::factory()->make()->getAttributes();
    $photo = match ($kind) {
        'svg' => UploadedFile::fake()->createWithContent('unsafe.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'),
        'size' => UploadedFile::fake()->image('large.png')->size(2049),
        'dimensions' => UploadedFile::fake()->image('wide.png', 4097, 1),
    };

    $response = $this->actingAs($user)->post(route('admin.products.store'), [...$data, 'photo' => $photo]);

    $response->assertSessionHasErrors('photo');
    $this->assertDatabaseCount('products', 0);
    expect(Storage::disk('local')->allFiles())->toBeEmpty();
})->with(['svg', 'size', 'dimensions']);

it('updates a product with its own identifiers and keeps the existing photo', function () {
    $user = User::factory()->admin()->create();
    Storage::fake('local');
    Storage::disk('local')->put('products/existing.png', 'photo');
    $product = Product::factory()->create(['photo_path' => 'products/existing.png']);
    $data = $product->only(['sku', 'name', 'description', 'category_id', 'weight', 'length', 'width', 'height', 'price', 'barcode', 'rack_location', 'principal_name']);

    $response = $this->actingAs($user)->put(route('admin.products.update', $product), [...$data, 'name' => 'Produk diperbarui']);

    $response->assertRedirect(route('admin.products.index'))->assertSessionHas('status', 'Produk berhasil diperbarui.');
    $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Produk diperbarui', 'photo_path' => 'products/existing.png']);
    Storage::disk('local')->assertExists('products/existing.png');
});

it('replaces an old photo after successfully saving a new one', function () {
    $user = User::factory()->admin()->create();
    Storage::fake('local');
    Storage::disk('local')->put('products/old.png', 'old photo');
    $product = Product::factory()->create(['photo_path' => 'products/old.png']);
    $data = $product->only(['sku', 'name', 'description', 'category_id', 'weight', 'length', 'width', 'height', 'price', 'barcode', 'rack_location', 'principal_name']);

    $response = $this->actingAs($user)->put(route('admin.products.update', $product), [
        ...$data, 'photo' => UploadedFile::fake()->image('replacement.png'),
    ]);

    $response->assertRedirect(route('admin.products.index'))->assertSessionHasNoErrors();
    $product->refresh();
    Storage::disk('local')->assertExists($product->photo_path);
    Storage::disk('local')->assertMissing('products/old.png');
});

it('stores a product when optional fields and photo are empty', function () {
    $user = User::factory()->admin()->create();
    $data = Product::factory()->make(['description' => null, 'barcode' => null, 'rack_location' => null])->getAttributes();

    $response = $this->actingAs($user)->post(route('admin.products.store'), $data);

    $response->assertRedirect(route('admin.products.index'))->assertSessionHasNoErrors();
    $this->assertDatabaseHas('products', ['sku' => $data['sku'], 'barcode' => null, 'photo_path' => null]);
});

it('stores product variations with their own sku price and optional weight', function () {
    $user = User::factory()->admin()->create();
    $data = Product::factory()->make()->getAttributes();

    $response = $this->actingAs($user)->post(route('admin.products.store'), [
        ...$data,
        'variations_present' => 1,
        'variations' => [
            ['name' => 'Isi 30 tablet', 'sku' => 'sn-vit-30', 'price' => 32000, 'weight' => 125],
            ['name' => 'Isi 60 tablet', 'sku' => 'sn-vit-60', 'price' => 55000, 'weight' => ''],
        ],
    ]);

    $response->assertRedirect(route('admin.products.index'));
    $product = Product::query()->sole();
    $this->assertDatabaseHas('product_variations', [
        'product_id' => $product->id,
        'name' => 'Isi 30 tablet',
        'sku' => 'SN-VIT-30',
        'price' => 32000,
        'weight' => 125,
    ]);
    $this->assertDatabaseHas('product_variations', [
        'product_id' => $product->id,
        'name' => 'Isi 60 tablet',
        'sku' => 'SN-VIT-60',
        'price' => 55000,
        'weight' => null,
    ]);
});

it('replaces product variations when an edit removes them', function () {
    $user = User::factory()->admin()->create();
    $product = Product::factory()->create();
    $product->variations()->create([
        'name' => 'Isi 30 tablet',
        'sku' => 'SN-VIT-30',
        'price' => 32000,
    ]);
    $data = $product->only(['sku', 'name', 'description', 'category_id', 'weight', 'length', 'width', 'height', 'price', 'barcode', 'rack_location', 'principal_name']);

    $response = $this->actingAs($user)->put(route('admin.products.update', $product), [
        ...$data,
        'variations_present' => 1,
    ]);

    $response->assertRedirect(route('admin.products.index'));
    $this->assertDatabaseCount('product_variations', 0);
});

it('rejects a variation sku already used by another product', function () {
    $user = User::factory()->admin()->create();
    $existingProduct = Product::factory()->create();
    $existingProduct->variations()->create([
        'name' => 'Isi 30 tablet',
        'sku' => 'SN-VIT-30',
        'price' => 32000,
    ]);
    $data = Product::factory()->make()->getAttributes();

    $response = $this->actingAs($user)->post(route('admin.products.store'), [
        ...$data,
        'variations_present' => 1,
        'variations' => [
            ['name' => 'Variasi bentrok', 'sku' => 'sn-vit-30', 'price' => 45000],
        ],
    ]);

    $response->assertSessionHasErrors('variations.0.sku');
    $this->assertDatabaseCount('products', 1);
    $this->assertDatabaseCount('product_variations', 1);
});

it('rejects conflicting identifiers on update and keeps the existing photo', function () {
    $user = User::factory()->admin()->create();
    Storage::fake('local');
    Storage::disk('local')->put('products/kept.png', 'existing photo');
    $other = Product::factory()->create();
    $product = Product::factory()->create(['photo_path' => 'products/kept.png']);
    $data = $product->only(['sku', 'name', 'description', 'category_id', 'weight', 'length', 'width', 'height', 'price', 'barcode', 'rack_location', 'principal_name']);

    $response = $this->actingAs($user)->put(route('admin.products.update', $product), [
        ...$data, 'sku' => $other->sku, 'barcode' => $other->barcode,
        'photo' => UploadedFile::fake()->image('not-saved.png'),
    ]);

    $response->assertSessionHasErrors(['sku', 'barcode']);
    $this->assertDatabaseHas('products', ['id' => $product->id, 'sku' => $product->sku, 'photo_path' => 'products/kept.png']);
    expect(Storage::disk('local')->allFiles())->toBe(['products/kept.png']);
});

it('serves saved product photos inline from private local storage', function () {
    $user = User::factory()->admin()->create();
    Storage::fake('local');
    $photoPath = UploadedFile::fake()->image('product.png')->store('products', 'local');
    $product = Product::factory()->create(['photo_path' => $photoPath]);

    $this->actingAs($user)->get(route('admin.products.photo', $product))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png')
        ->assertHeader('Content-Disposition', 'inline')
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});

it('returns not found when a product photo is missing or outside its storage directory', function (string $photoPath) {
    $user = User::factory()->admin()->create();
    Storage::fake('local');
    $product = Product::factory()->create(['photo_path' => $photoPath]);

    $this->actingAs($user)->get(route('admin.products.photo', $product))->assertNotFound();
})->with([
    'missing file' => ['products/missing.png'],
    'outside product directory' => ['store/logo.png'],
    'path traversal' => ['products/../private.png'],
]);

it('deletes a product together with its variations and local photo', function () {
    $user = User::factory()->admin()->create();
    Storage::fake('local');
    Storage::disk('local')->put('products/remove.png', 'product photo');
    $product = Product::factory()->create(['photo_path' => 'products/remove.png']);
    $product->variations()->create([
        'name' => 'Isi 30 tablet',
        'sku' => 'SN-DELETE-30',
        'price' => 32000,
    ]);

    $this->actingAs($user)->delete(route('admin.products.destroy', $product))
        ->assertRedirect(route('admin.products.index'))
        ->assertSessionHas('status', 'Produk berhasil dihapus.');

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
    $this->assertDatabaseCount('product_variations', 0);
    Storage::disk('local')->assertMissing('products/remove.png');
});

it('restricts product mutations and image access to users with the right permissions', function () {
    $this->seed(RolePermissionSeeder::class);
    Storage::fake('local');
    $photoPath = UploadedFile::fake()->image('product.png')->store('products', 'local');
    $readerRole = Role::create(['name' => 'product-reader', 'guard_name' => 'web']);
    $readerRole->givePermissionTo('view products');
    $reader = User::factory()->create();
    $reader->assignRole($readerRole);
    $product = Product::factory()->create(['photo_path' => $photoPath]);
    $customer = User::factory()->create();

    $this->actingAs($reader)->get(route('admin.products.index'))
        ->assertOk()
        ->assertDontSee(route('admin.products.create'))
        ->assertDontSee(route('admin.products.edit', $product))
        ->assertDontSee('data-confirm-delete', escape: false);
    $this->get(route('admin.products.export'))->assertDownload('products.csv');
    $this->get(route('admin.products.photo', $product))->assertOk();
    $this->delete(route('admin.products.destroy', $product))->assertForbidden();
    $this->assertDatabaseHas('products', ['id' => $product->id]);
    $this->actingAs($customer)->get(route('admin.products.export'))->assertForbidden();
});
