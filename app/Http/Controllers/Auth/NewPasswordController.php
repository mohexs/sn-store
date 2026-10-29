<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreNewPasswordRequest;
use App\Services\Auth\PasswordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /** Handle an incoming new password request. */
    public function store(StoreNewPasswordRequest $request, PasswordService $service): RedirectResponse
    {
        $status = $service->reset($request->credentials());

        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', 'Password berhasil direset. Silakan login dengan password baru.')
                    : back()->withInput($request->safe()->only('email'))
                        ->withErrors(['email' => 'Link reset tidak valid atau sudah kedaluwarsa. Silakan minta link reset baru.']);
    }
}
