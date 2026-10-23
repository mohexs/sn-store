<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminAccountRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AdminAccountController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.admin-accounts.index', [
            'admins' => User::query()
                ->where(fn (Builder $query) => $query->where('is_admin', true)
                    ->orWhereHas('roles', fn (Builder $roles) => $roles->where('name', 'admin')->where('guard_name', 'web')))
                ->latest('id')->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('pages.admin.admin-accounts.create');
    }

    public function store(StoreAdminAccountRequest $request): RedirectResponse
    {
        $admin = new User($request->validated());
        $admin->save();
        $admin->assignRole(Role::findOrCreate('admin', 'web'));

        return to_route('admin.admin-accounts.index')->with('status', 'Akun admin berhasil dibuat. Admin baru dapat login menggunakan email dan password yang ditentukan.');
    }
}
