<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function home(Request $request): View|RedirectResponse
    {
        return $request->user()
            ? redirect()->route('dashboard')
            : view('home');
    }

    public function verifyNotice(Request $request): View|RedirectResponse
    {
        return $request->user()->hasVerifiedEmail()
            ? redirect()->route('dashboard')
            : view('account.verify-email');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect()->route('dashboard')->with('status', 'Merci, ton adresse e-mail est confirmée.');
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Un nouveau lien de confirmation vient de t\'être envoyé.');
    }

    public function dashboard(Request $request): View
    {
        $user = $request->user();

        return view('account.dashboard', [
            'user' => $user,
            'profile' => $user->ensureProfile(),
        ]);
    }

    public function pending(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        return $user->isApproved() || $user->is_admin
            ? redirect()->route('dashboard')
            : view('account.pending', ['user' => $user]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
