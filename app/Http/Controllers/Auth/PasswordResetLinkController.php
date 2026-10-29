<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StorePasswordResetLinkRequest;
use App\Services\Auth\PasswordService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /** Handle an incoming password reset link request. */
    public function store(StorePasswordResetLinkRequest $request, PasswordService $service): RedirectResponse
    {
        $status = $service->sendResetLink($request->validated('email'));

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', 'Link reset password telah dikirim. Periksa inbox atau folder spam email Anda.')
                    : back()->withInput($request->safe()->only('email'))
                        ->withErrors(['email' => $status === Password::RESET_THROTTLED
                            ? 'Tunggu sebentar sebelum meminta link reset kembali.'
                            : 'Kami tidak menemukan akun dengan email tersebut.']);
    }
}
