<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Services\Auth\PasswordService;
use Illuminate\Http\RedirectResponse;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(UpdatePasswordRequest $request, PasswordService $service): RedirectResponse
    {
        $service->update($request->user(), $request->validated('password'));

        return back()->with('status', 'password-updated');
    }
}
