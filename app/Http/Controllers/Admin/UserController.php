<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $search = trim($filters['search'] ?? '');

        return view('pages.admin.users.index', [
            'search' => $search,
            'users' => User::query()->where('is_admin', false)
                ->whereDoesntHave('roles', fn (Builder $roles) => $roles->where('name', 'admin')->where('guard_name', 'web'))
                ->when($search !== '', function (Builder $query) use ($search): void {
                    $query->where(function (Builder $query) use ($search): void {
                        $query->whereLike('name', '%'.$search.'%')
                            ->orWhereLike('email', '%'.$search.'%')
                            ->orWhereLike('phone', '%'.$search.'%')
                            ->orWhereLike('city', '%'.$search.'%');
                    });
                })
                ->latest('id')->paginate(10)->withQueryString(),
        ]);
    }

    public function edit(User $user): View
    {
        abort_if($user->is_admin || $user->hasRole('admin'), 404);

        return view('pages.admin.users.edit', ['customer' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        abort_if($user->is_admin || $user->hasRole('admin'), 404);
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('admin.users.edit', $user)->with('status', 'Profil customer berhasil diperbarui.');
    }
}
