<?php

namespace App\Services\Admin;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ProductService
{
    private const PHOTO_DISK = 'local';

    public function paginate(): LengthAwarePaginator
    {
        return Product::query()->with('category')->latest('id')->paginate(10);
    }

    /** @return Collection<int, Category> */
    public function categories(): Collection
    {
        return Category::query()->orderBy('name')->get(['id', 'name']);
    }

    public function exportCsv(): StreamedResponse
    {
        $headers = [
            'product_id', 'sku', 'name', 'description',
            'category_id', 'category_name', 'category_name_key', 'category_slug',
            'category_description', 'category_created_at', 'category_updated_at',
            'weight', 'length', 'width', 'height', 'price', 'photo_path',
            'barcode', 'rack_location', 'principal_name', 'created_at', 'updated_at',
            'variation_id', 'variation_product_id', 'variation_name', 'variation_sku',
            'variation_price', 'variation_weight', 'variation_created_at', 'variation_updated_at',
        ];

        return response()->streamDownload(function () use ($headers): void {
            $output = fopen('php://output', 'wb');

            if ($output === false) {
                throw new RuntimeException('Unable to open output stream for product export.');
            }

            try {
                if (fwrite($output, "\xEF\xBB\xBF") === false || fputcsv($output, $headers, ',', '"', '', "\r\n") === false) {
                    throw new RuntimeException('Unable to write product export headers.');
                }

                Product::query()
                    ->with(['category', 'variations'])
                    ->lazyById(200)
                    ->each(function (Product $product) use ($output): void {
                        $baseRow = [
                            $product->id,
                            $product->sku,
                            $product->name,
                            $product->description,
                            $product->category_id,
                            $product->category->name,
                            $product->category->name_key,
                            $product->category->slug,
                            $product->category->description,
                            $product->category->created_at?->toDateTimeString(),
                            $product->category->updated_at?->toDateTimeString(),
                            $product->weight,
                            $product->length,
                            $product->width,
                            $product->height,
                            $product->price,
                            $product->photo_path,
                            $product->barcode,
                            $product->rack_location,
                            $product->principal_name,
                            $product->created_at?->toDateTimeString(),
                            $product->updated_at?->toDateTimeString(),
                        ];
                        $variations = $product->variations;

                        if ($variations->isEmpty()) {
                            $this->writeExportRow($output, [...$baseRow, null, null, null, null, null, null, null, null]);

                            return;
                        }

                        foreach ($variations as $variation) {
                            $this->writeExportRow($output, [
                                ...$baseRow,
                                $variation->id,
                                $variation->product_id,
                                $variation->name,
                                $variation->sku,
                                $variation->price,
                                $variation->weight,
                                $variation->created_at?->toDateTimeString(),
                                $variation->updated_at?->toDateTimeString(),
                            ]);
                        }
                    });
            } finally {
                fclose($output);
            }
        }, 'products.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** @return array<int, array<string, mixed>> */
    public function variations(Product $product): array
    {
        $product->load('variations');

        return $product->variations
            ->map(fn ($variation): array => $variation->only(['name', 'sku', 'price', 'weight']))
            ->all();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, ?UploadedFile $photo): Product
    {
        return $this->save(new Product, $data, $photo);
    }

    /** @param array<string, mixed> $data */
    public function update(Product $product, array $data, ?UploadedFile $photo): Product
    {
        return $this->save($product, $data, $photo);
    }

    /** @return array{path: string, mime_type: string}|null */
    public function photo(Product $product): ?array
    {
        $photoPath = $product->photo_path;

        if (! is_string($photoPath) || preg_match('/\Aproducts\/[A-Za-z0-9_-]+\.(?:jpe?g|png|webp)\z/i', $photoPath) !== 1) {
            return null;
        }

        $disk = Storage::disk(self::PHOTO_DISK);

        if (! $disk->exists($photoPath)) {
            return null;
        }

        $mimeType = $disk->mimeType($photoPath);

        if (! in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        return ['path' => $disk->path($photoPath), 'mime_type' => $mimeType];
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product): void {
            $product->deleteOrFail();
        });

        if ($product->photo_path !== null) {
            $this->deletePhoto($product->photo_path);
        }
    }

    /** @param array<string, mixed> $data */
    private function save(Product $product, array $data, ?UploadedFile $photo): Product
    {
        $oldPhoto = $product->photo_path;
        $newPhoto = null;
        $variations = $data['variations'] ?? null;
        unset($data['variations']);

        if ($photo !== null) {
            $newPhoto = $photo->store('products', self::PHOTO_DISK);

            if ($newPhoto === false) {
                throw ValidationException::withMessages(['photo' => 'Foto gagal disimpan. Silakan coba kembali.']);
            }
        }

        try {
            DB::transaction(function () use ($product, $data, $newPhoto, $variations): void {
                $product->fill($data);

                if ($newPhoto !== null) {
                    $product->photo_path = $newPhoto;
                }

                $product->saveOrFail();

                if ($variations !== null) {
                    $product->variations()->delete();
                    $product->variations()->createMany($variations);
                }
            });
        } catch (Throwable $exception) {
            if ($newPhoto !== null) {
                try {
                    $this->deletePhoto($newPhoto);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            throw $exception;
        }

        if ($newPhoto !== null && $oldPhoto !== null) {
            $this->deletePhoto($oldPhoto);
        }

        return $product;
    }

    private function deletePhoto(string $path): void
    {
        $disk = Storage::disk(self::PHOTO_DISK);

        if ($disk->exists($path) && ! $disk->delete($path)) {
            throw new RuntimeException("Unable to delete product photo [{$path}].");
        }
    }

    /**
     * @param  resource  $output
     * @param  array<int, mixed>  $row
     */
    private function writeExportRow($output, array $row): void
    {
        $row = array_map(function (mixed $value): string {
            $cell = $value === null ? '' : (string) $value;

            return preg_match('/\A[\x00-\x20]*[=+\-@]/', $cell) === 1 ? "'".$cell : $cell;
        }, $row);

        if (fputcsv($output, $row, ',', '"', '', "\r\n") === false) {
            throw new RuntimeException('Unable to write product export row.');
        }
    }
}
