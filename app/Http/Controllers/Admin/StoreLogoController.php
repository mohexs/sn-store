<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateStoreLogoRequest;
use App\Services\Admin\StoreLogoService;
use Illuminate\Http\RedirectResponse;

class StoreLogoController extends Controller
{
    public function update(UpdateStoreLogoRequest $request, StoreLogoService $service): RedirectResponse
    {
        $service->update($request->file('logo'));

        return to_route('admin.themes.index')->with('status', 'Gambar identitas toko berhasil diperbarui.');
    }
}
