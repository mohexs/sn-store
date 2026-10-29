<?php

namespace App\Services\Admin;

use App\Models\HeroAd;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class HeroAdService
{
    public function paginate(): LengthAwarePaginator
    {
        return HeroAd::query()->orderBy('sort_order')->orderBy('id')->paginate(10);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, ?UploadedFile $image): HeroAd
    {
        return $this->save(new HeroAd, $data, $image);
    }

    /** @param array<string, mixed> $data */
    public function update(HeroAd $ad, array $data, ?UploadedFile $image): HeroAd
    {
        return $this->save($ad, $data, $image);
    }

    public function delete(HeroAd $ad): void
    {
        $imagePath = $ad->image_path;
        $ad->delete();
        Storage::disk('public')->delete($imagePath);
    }

    /** @param array<string, mixed> $data */
    private function save(HeroAd $ad, array $data, ?UploadedFile $image): HeroAd
    {
        $oldImagePath = $ad->image_path;
        $newImagePath = $image?->store('hero-ads', 'public');

        if ($image !== null && $newImagePath === false) {
            throw ValidationException::withMessages([
                'image' => 'Foto banner gagal disimpan. Silakan coba kembali.',
            ]);
        }

        try {
            $ad->fill($data);

            if ($newImagePath !== null) {
                $ad->image_path = $newImagePath;
            }

            $ad->saveOrFail();
        } catch (Throwable $exception) {
            if ($newImagePath !== null) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw $exception;
        }

        if ($newImagePath !== null && $oldImagePath !== null) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return $ad;
    }
}
