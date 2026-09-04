<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $intended = $request->session()->get('url.intended');

        if (Auth::check()) {
            if ($intended) {
                return redirect()->intended();
            }

            $frontendUrl = rtrim((string) (config('app.frontend_url') ?: env('FRONTEND_URL')), '/');
            $targetInterface = $request->query('target_interface') ?? $request->input('target_interface');

            if ($targetInterface === 'OI') {
                return redirect($frontendUrl . '/#/portal');
            }
            return redirect($frontendUrl . '/#/dashboard/workspace');
        }

        // Detect which interface the user came from based on the intended URL state
        $defaultInterface = 'UI';
        $intended = $request->session()->get('url.intended');
        if ($intended && (str_contains($intended, 'state=%2Fportal') || str_contains($intended, 'state=/portal'))) {
            $defaultInterface = 'OI';
        }

        $frontendUrl = rtrim((string) (config('app.frontend_url') ?: env('FRONTEND_URL')), '/');

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
            'defaultInterface' => $defaultInterface,
            'frontendUrl' => $frontendUrl,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): \Symfony\Component\HttpFoundation\Response
    {
        $request->authenticate();

        $request->session()->regenerate();

        $frontendUrl = rtrim((string) (config('app.frontend_url') ?: env('FRONTEND_URL')), '/');
        $targetInterface = $request->input('target_interface', 'UI');
        $targetPath = ($targetInterface === 'OI') ? '/portal' : '/dashboard/workspace';

        $intended = $request->session()->get('url.intended');

        if ($intended) {
            // Always override state with the user's explicit OI/UI choice.
            if (str_contains($intended, 'state=')) {
                $intended = preg_replace('/state=[^&]*/', 'state=' . urlencode($targetPath), $intended);
            } else {
                $separator = str_contains($intended, '?') ? '&' : '?';
                $intended .= $separator . 'state=' . urlencode($targetPath);
            }
            $request->session()->put('url.intended', $intended);
            $redirectUrl = redirect()->intended()->getTargetUrl();
        } else {
            $redirectUrl = $frontendUrl . '/#' . $targetPath;
        }

        return Inertia::location($redirectUrl);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $frontendUrl = config('app.frontend_url') ?: env('FRONTEND_URL', '/');

        return redirect($frontendUrl);
    }
}
