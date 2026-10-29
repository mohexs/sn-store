<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('requires login to access category management', function (string $method) {
    $this->call($method, route('admin.categories.index'))->assertRedirect(route('login'));
})->with(['GET', 'POST']);

it('lists categories with the number of linked products and escapes content', function () {
    $user = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => '<script>alert("unsafe")</script>']);
    Product::factory()->count(2)->for($category)->create();

    $response = $this->actingAs($user)->get(route('admin.categories.index'));

    $response->assertOk()
        ->assertViewIs('pages.admin.categories.index')
        ->assertSee($category->name)
        ->assertDontSee($category->name, escape: false)
        ->assertViewHas('categories', fn ($categories) => $categories->firstWhere('id', $category->id)->products_count === 2);
});

it('stores a category with normalized whitespace', function () {
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)->post(route('admin.categories.store'), [
        'name' => '  Ibu   dan Bayi  ', 'description' => 'Kebutuhan ibu dan bayi.',
        'name_key' => 'untrusted', 'slug' => 'slug-manual',
    ]);

    $response->assertRedirect(route('admin.categories.index'))->assertSessionHasNoErrors();
    $this->assertDatabaseHas('categories', [
        'name' => 'Ibu dan Bayi', 'name_key' => 'ibu dan bayi',
        'slug' => 'ibu-dan-bayi', 'description' => 'Kebutuhan ibu dan bayi.',
    ]);
});

it('adds a numeric suffix when category names produce the same slug', function () {
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)->post(route('admin.categories.store'), [
        'name' => 'Vitamin-Suplemen',
    ]);

    $response->assertRedirect(route('admin.categories.index'))->assertSessionHasNoErrors();
    $this->assertDatabaseHas('categories', ['name' => 'Vitamin-Suplemen', 'slug' => 'vitamin-suplemen-2']);
});

it('rejects duplicate category names regardless of casing or whitespace', function () {
    $user = User::factory()->admin()->create();
    Category::factory()->create(['name' => 'Perawatan Lansia']);

    $response = $this->actingAs($user)->post(route('admin.categories.store'), ['name' => '  perawatan   LANSIA ']);

    $response->assertSessionHasErrors(['name' => 'Nama kategori sudah digunakan.']);
    expect(Category::query()->where('name_key', 'perawatan lansia')->count())->toBe(1);
});

it('rejects invalid category information', function (array $data, string $field) {
    $user = User::factory()->admin()->create();
    $before = Category::query()->count();

    $response = $this->actingAs($user)->post(route('admin.categories.store'), $data);

    $response->assertSessionHasErrors($field);
    $this->assertDatabaseCount('categories', $before);
})->with([
    'missing name' => [[], 'name'],
    'blank name' => [['name' => '   '], 'name'],
    'long name' => [['name' => str_repeat('a', 101)], 'name'],
    'long description' => [['name' => 'Baru', 'description' => str_repeat('a', 2001)], 'description'],
]);

it('offers database categories in the product creation dropdown', function () {
    $user = User::factory()->admin()->create();
    $category = Category::factory()->create(['name' => 'Kategori Baru Khusus']);

    $response = $this->actingAs($user)->get(route('admin.products.create'));

    $response->assertOk()->assertSee('value="'.$category->id.'"', escape: false)->assertSee($category->name);
});

it('selects the linked category when editing a product', function () {
    $user = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();

    $response = $this->actingAs($user)->get(route('admin.products.edit', $product));

    $response->assertOk()->assertSee('value="'.$category->id.'" selected', escape: false);
});

it('preserves legacy product categories during migration and rollback', function () {
    $originalConnection = DB::getDefaultConnection();
    config(['database.connections.category_migration_check' => array_replace(config('database.connections.sqlite'), ['database' => ':memory:'])]);
    DB::setDefaultConnection('category_migration_check');
    Schema::clearResolvedInstance('db.schema');

    try {
        $legacy = require database_path('migrations/2026_10_21_090049_create_products_table.php');
        $legacy->up();
        DB::table('products')->insert([
            'sku' => 'LEGACY-01', 'name' => 'Produk lama', 'category' => 'Kategori Lama Khusus',
            'weight' => 100, 'length' => 10, 'width' => 5, 'height' => 3,
            'price' => 12500, 'principal_name' => 'Principal Lama',
        ]);
        $migration = require database_path('migrations/2026_10_21_091552_create_categories_table.php');

        $migration->up();

        $categoryId = DB::table('categories')->where('name', 'Kategori Lama Khusus')->value('id');
        expect($categoryId)->not->toBeNull();
        $this->assertDatabaseHas('products', ['sku' => 'LEGACY-01', 'category_id' => $categoryId, 'price' => 12500]);

        DB::table('categories')->insert([
            'name' => 'Vitamin-Suplemen',
            'name_key' => 'vitamin-suplemen',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $slugMigration = require database_path('migrations/2026_10_28_151420_add_slug_to_categories_table.php');
        $slugMigration->up();

        $this->assertDatabaseHas('categories', ['name' => 'Kategori Lama Khusus', 'slug' => 'kategori-lama-khusus']);
        $this->assertDatabaseHas('categories', ['name' => 'Vitamin-Suplemen', 'slug' => 'vitamin-suplemen-2']);

        $slugMigration->down();
        $migration->down();

        $this->assertDatabaseHas('products', ['sku' => 'LEGACY-01', 'category' => 'Kategori Lama Khusus']);
    } finally {
        Schema::clearResolvedInstance('db.schema');
        DB::purge('category_migration_check');
        DB::setDefaultConnection($originalConnection);
    }
});

it('requires login before editing a category', function (string $method, string $path) {
    $this->call($method, $path)->assertRedirect(route('login'));
})->with([
    'edit form' => ['GET', '/admin/categories/1/edit'],
    'update' => ['PUT', '/admin/categories/1'],
]);

it('shows the saved category details in the edit form', function () {
    $user = User::factory()->admin()->create();
    $category = Category::factory()->create(['description' => '<script>alert("unsafe")</script>']);

    $response = $this->actingAs($user)->get(route('admin.categories.edit', $category));

    $response->assertOk()
        ->assertViewIs('pages.admin.categories.edit')
        ->assertSee($category->name)
        ->assertSee($category->description)
        ->assertDontSee('name="slug"')
        ->assertDontSee($category->description, escape: false);
});

it('updates category details while keeping existing products linked', function () {
    $user = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();

    $response = $this->actingAs($user)->put(route('admin.categories.update', $category), [
        'name' => '  Kategori   Diperbarui  ', 'description' => 'Deskripsi baru.',
        'name_key' => 'untrusted', 'slug' => 'slug-manual',
    ]);

    $response->assertRedirect(route('admin.categories.index'))->assertSessionHasNoErrors();
    $this->assertDatabaseHas('categories', [
        'id' => $category->id, 'name' => 'Kategori Diperbarui',
        'name_key' => 'kategori diperbarui', 'slug' => 'kategori-diperbarui',
        'description' => 'Deskripsi baru.',
    ]);
    expect($product->fresh()->category->name)->toBe('Kategori Diperbarui');
});

it('allows saving a category with its existing name', function () {
    $user = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($user)->put(route('admin.categories.update', $category), [
        'name' => $category->name, 'description' => null,
    ]);

    $response->assertRedirect(route('admin.categories.index'))->assertSessionHasNoErrors();
    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => $category->name, 'description' => null]);
});

it('rejects another category name during update regardless of case and whitespace', function () {
    $user = User::factory()->admin()->create();
    Category::factory()->create(['name' => 'Kategori Terpakai']);
    $category = Category::factory()->create();

    $response = $this->actingAs($user)->put(route('admin.categories.update', $category), [
        'name' => ' kategori  TERPAKAI ',
    ]);

    $response->assertSessionHasErrors(['name' => 'Nama kategori sudah digunakan.']);
    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => $category->name]);
});

it('rejects a blank name when updating a category', function () {
    $user = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $response = $this->actingAs($user)->put(route('admin.categories.update', $category), ['name' => '  ']);

    $response->assertSessionHasErrors(['name' => 'Nama kategori wajib diisi.']);
    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => $category->name]);
});

it('returns not found for a missing category', function (string $method, string $path) {
    $user = User::factory()->admin()->create();

    $this->actingAs($user)->call($method, $path)->assertNotFound();
})->with([
    'edit' => ['GET', '/admin/categories/999999/edit'],
    'update' => ['PUT', '/admin/categories/999999'],
]);
