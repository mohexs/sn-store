<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('name_key', 100)->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained()->restrictOnDelete();
        });

        $names = collect(['Obat-obatan', 'Vitamin & Suplemen', 'Alat Kesehatan', 'Perawatan Diri'])
            ->merge(DB::table('products')->distinct()->pluck('category'))->unique();

        foreach ($names as $name) {
            $normalized = preg_replace('/\s+/u', ' ', trim($name));
            $key = mb_strtolower($normalized);
            DB::table('categories')->insertOrIgnore([
                'name' => $normalized, 'name_key' => $key,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $id = DB::table('categories')->where('name_key', $key)->value('id');
            DB::table('products')->where('category', $name)->update(['category_id' => $id]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
            $table->dropColumn('category');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('category', 100)->default('');
        });

        foreach (DB::table('categories')->get(['id', 'name']) as $category) {
            DB::table('products')->where('category_id', $category->id)->update(['category' => $category->name]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });

        Schema::dropIfExists('categories');
    }
};
