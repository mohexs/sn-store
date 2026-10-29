<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHeroAdRequest;
use App\Http\Requests\Admin\UpdateHeroAdRequest;
use App\Models\HeroAd;
use App\Services\Admin\HeroAdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HeroAdController extends Controller
{
    public function index(HeroAdService $service): View
    {
        return view('pages.admin.ads.index', ['ads' => $service->paginate()]);
    }

    public function create(): View
    {
        return view('pages.admin.ads.create', ['ad' => new HeroAd]);
    }

    public function store(StoreHeroAdRequest $request, HeroAdService $service): RedirectResponse
    {
        $service->create($request->safe()->except('image'), $request->file('image'));

        return to_route('admin.ads.index')->with('status', 'Banner hero berhasil ditambahkan.');
    }

    public function edit(HeroAd $ad): View
    {
        return view('pages.admin.ads.edit', compact('ad'));
    }

    public function update(UpdateHeroAdRequest $request, HeroAd $ad, HeroAdService $service): RedirectResponse
    {
        $service->update($ad, $request->safe()->except('image'), $request->file('image'));

        return to_route('admin.ads.index')->with('status', 'Banner hero berhasil diperbarui.');
    }

    public function destroy(HeroAd $ad, HeroAdService $service): RedirectResponse
    {
        $service->delete($ad);

        return to_route('admin.ads.index')->with('status', 'Banner hero berhasil dihapus.');
    }
}
