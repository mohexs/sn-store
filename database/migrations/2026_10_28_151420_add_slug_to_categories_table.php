<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable();
        });

        foreach (DB::table('categories')->orderBy('id')->get(['id', 'name']) as $category) {
            $baseSlug = Str::slug($category->name) ?: 'kategori';
            $slug = $baseSlug;
            $suffix = 2;

            while (DB::table('categories')->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$suffix++;
            }

            DB::table('categories')->where('id', $category->id)->update(['slug' => $slug]);
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_slug_unique');
            $table->dropColumn('slug');
        });
    }
};
