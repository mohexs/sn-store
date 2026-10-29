<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminAccountRequest;
use App\Services\Admin\AdminAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminAccountController extends Controller
{
    public function index(AdminAccountService $service): View
    {
        return view('pages.admin.admin-accounts.index', [
            'admins' => $service->admins(),
        ]);
    }

    public function create(AdminAccountService $service): View
    {
        return view('pages.admin.admin-accounts.create', ['adminRoles' => $service->roles()]);
    }

    public function store(StoreAdminAccountRequest $request, AdminAccountService $service): RedirectResponse
    {
        $service->create($request->validated());

        return to_route('admin.admin-accounts.index')->with('status', 'Akun admin berhasil dibuat dengan jenis akses yang dipilih.');
    }
}
