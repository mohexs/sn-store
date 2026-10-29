<?php

namespace App\Services\Admin;

use App\Models\StoreSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class StoreLogoService
{
    public function update(?UploadedFile $logo): void
    {
        $newLogoPath = $logo?->storePublicly('store', 'public');

        if ($logo !== null && $newLogoPath === false) {
            throw ValidationException::withMessages(['logo' => 'Logo gagal disimpan. Silakan coba kembali.']);
        }

        $storeSetting = StoreSetting::query()->find(1) ?? new StoreSetting;

        if (! $storeSetting->exists) {
            $storeSetting->id = 1;
        }

        $oldLogoPath = $storeSetting->logo_path;

        try {
            $storeSetting->logo_path = $newLogoPath;
            $storeSetting->saveOrFail();
        } catch (Throwable $exception) {
            if ($newLogoPath !== null) {
                Storage::disk('public')->delete($newLogoPath);
            }

            throw $exception;
        }

        if ($newLogoPath !== null && $oldLogoPath !== null) {
            Storage::disk('public')->delete($oldLogoPath);
        }
    }
}
