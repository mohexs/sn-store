<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectAfterVerification($request, $user->can('access admin'));
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->redirectAfterVerification($request, $user->can('access admin'));
    }

    private function redirectAfterVerification(EmailVerificationRequest $request, bool $canAccessAdmin): RedirectResponse
    {
        if (! $canAccessAdmin) {
            return redirect()->route('home', ['verified' => 1]);
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
