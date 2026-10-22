<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function create(): mixed
    {
        return view('admin.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email:rfc'], 'password' => ['required', 'string']]);
        if (! Auth::attempt($data, $request->boolean('remember')) || ! $request->user()->isPortfolioAdmin()) {
            Auth::logout();

            return back()->withErrors(['email' => __('admin.invalid_credentials')])->onlyInput('email');
        }
        $request->session()->regenerate();

        return redirect()->intended(route('admin.projects.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
