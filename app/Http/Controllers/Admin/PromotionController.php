<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePromotionRequest;
use App\Http\Requests\Admin\UpdatePromotionRequest;
use App\Models\Promotion;
use App\Services\Admin\PromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(PromotionService $service): View
    {
        return view('pages.admin.promotions.index', ['promotions' => $service->paginate()]);
    }

    public function create(): View
    {
        return view('pages.admin.promotions.create', ['promotion' => new Promotion]);
    }

    public function store(StorePromotionRequest $request, PromotionService $service): RedirectResponse
    {
        $service->create($request->validated());

        return to_route('admin.promotions.index')->with('status', 'Program diskon berhasil ditambahkan.');
    }

    public function edit(Promotion $promotion): View
    {
        return view('pages.admin.promotions.edit', compact('promotion'));
    }

    public function update(UpdatePromotionRequest $request, Promotion $promotion, PromotionService $service): RedirectResponse
    {
        $service->update($promotion, $request->validated());

        return to_route('admin.promotions.index')->with('status', 'Program diskon berhasil diperbarui.');
    }

    public function destroy(Promotion $promotion, PromotionService $service): RedirectResponse
    {
        $service->delete($promotion);

        return to_route('admin.promotions.index')->with('status', 'Program diskon berhasil dihapus.');
    }
}
