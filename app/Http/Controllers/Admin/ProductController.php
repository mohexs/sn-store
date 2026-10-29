<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DeleteProductRequest;
use App\Http\Requests\Admin\ExportProductsRequest;
use App\Http\Requests\Admin\ShowProductPhotoRequest;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Services\Admin\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    public function index(ProductService $service): View
    {
        return view('pages.admin.products.index', ['products' => $service->paginate()]);
    }

    public function export(ExportProductsRequest $request, ProductService $service): StreamedResponse
    {
        return $service->exportCsv();
    }

    public function create(ProductService $service): View
    {
        return view('pages.admin.products.create', [
            'product' => new Product,
            'categories' => $service->categories(),
            'variations' => [],
        ]);
    }

    public function store(StoreProductRequest $request, ProductService $service): RedirectResponse
    {
        $service->create($request->safe()->only([
            'sku', 'name', 'description', 'category_id', 'weight', 'length', 'width',
            'height', 'price', 'barcode', 'rack_location', 'principal_name', 'variations',
        ]), $request->file('photo'));

        return to_route('admin.products.index')->with('status', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product, ProductService $service): View
    {
        return view('pages.admin.products.edit', [
            'product' => $product,
            'categories' => $service->categories(),
            'variations' => $service->variations($product),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product, ProductService $service): RedirectResponse
    {
        $service->update($product, $request->safe()->only([
            'sku', 'name', 'description', 'category_id', 'weight', 'length', 'width',
            'height', 'price', 'barcode', 'rack_location', 'principal_name', 'variations',
        ]), $request->file('photo'));

        return to_route('admin.products.index')->with('status', 'Produk berhasil diperbarui.');
    }

    public function destroy(DeleteProductRequest $request, Product $product, ProductService $service): RedirectResponse
    {
        $service->delete($product);

        return to_route('admin.products.index')->with('status', 'Produk berhasil dihapus.');
    }

    public function photo(ShowProductPhotoRequest $request, Product $product, ProductService $service): BinaryFileResponse
    {
        $photo = $service->photo($product);
        abort_unless($photo !== null, 404);

        return response()->file($photo['path'], [
            'Content-Type' => $photo['mime_type'],
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
