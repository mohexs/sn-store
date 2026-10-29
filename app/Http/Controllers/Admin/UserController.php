<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Http\Requests\Admin\UserIndexRequest;
use App\Models\User;
use App\Services\Admin\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(UserIndexRequest $request, UserService $service): View
    {
        $search = $request->validated('search', '');

        return view('pages.admin.users.index', [
            'search' => $search,
            'users' => $service->paginate($search),
        ]);
    }

    public function edit(User $user, UserService $service): View
    {
        $service->assertCustomer($user);

        return view('pages.admin.users.edit', ['customer' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user, UserService $service): RedirectResponse
    {
        $service->assertCustomer($user);
        $service->update($user, $request->validated());

        return to_route('admin.users.edit', $user)->with('status', 'Profil customer berhasil diperbarui.');
    }
}
