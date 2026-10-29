<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StoreRegisteredUserRequest;
use App\Services\Auth\AuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /** Handle an incoming registration request. */
    public function store(StoreRegisteredUserRequest $request, AuthenticationService $service): RedirectResponse
    {
        $service->register($request->validated());

        return redirect()->route('home');
    }
}
