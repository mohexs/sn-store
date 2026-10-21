<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.products.index', [
            'products' => Product::query()->with('category')->latest('id')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.products.create', [
            'product' => new Product,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->saveProduct(new Product, $request->safe()->except('photo'), $request->file('photo'));

        return to_route('admin.products.index')->with('status', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('pages.admin.products.edit', [
            'product' => $product,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->saveProduct($product, $request->safe()->except('photo'), $request->file('photo'));

        return to_route('admin.products.index')->with('status', 'Produk berhasil diperbarui.');
    }

    /** @param array<string, mixed> $data */
    private function saveProduct(Product $product, array $data, ?UploadedFile $photo): void
    {
        $oldPhoto = $product->photo_path;
        $newPhoto = null;

        if ($photo !== null) {
            $newPhoto = $photo->store('products', 'public');

            if ($newPhoto === false) {
                throw ValidationException::withMessages(['photo' => 'Foto gagal disimpan. Silakan coba kembali.']);
            }
        }

        try {
            $product->fill($data);

            if ($newPhoto !== null) {
                $product->photo_path = $newPhoto;
            }

            $product->saveOrFail();
        } catch (Throwable $exception) {
            if ($newPhoto !== null) {
                Storage::disk('public')->delete($newPhoto);
            }

            throw $exception;
        }

        if ($newPhoto !== null && $oldPhoto !== null) {
            Storage::disk('public')->delete($oldPhoto);
        }
    }
}
