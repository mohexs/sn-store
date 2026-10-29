<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\EmailVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request, EmailVerificationService $service): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            if (! $request->user()->can('access admin')) {
                return redirect()->route('home');
            }

            return redirect()->intended(route('dashboard', absolute: false));
        }

        $service->send($request->user());

        return back()->with('status', 'verification-link-sent');
    }
}
