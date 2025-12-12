<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function login()
    {
        if (Auth::check()) {
            return $this->redirectUser(Auth::user());
        }

        // Fetch office location for the map in the login/choice view
        $officeLocation = Setting::getOfficeLocation();

        $faceSetting = Setting::where('key', 'face_recognition_enabled')->first();
        $faceRecognitionEnabled = $faceSetting ? filter_var($faceSetting->value, FILTER_VALIDATE_BOOLEAN) : false;

        return view('auth.choice', compact('officeLocation', 'faceRecognitionEnabled'));
    }

    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect' => $this->getRedirectUrl(Auth::user()),
                ]);
            }

            return $this->redirectUser(Auth::user());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'The provided credentials do not match our records.',
                'errors' => ['email' => ['The provided credentials do not match our records.']]
            ], 422);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    protected function getRedirectUrl($user)
    {
        if ($user->role === 'admin') {
            return url('/admin');
        }
        return url('/user');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Redirect user based on role.
     */
    protected function redirectUser($user)
    {
        if ($user->role === 'admin') {
            return redirect()->intended('/admin');
        }

        return redirect()->intended('/user');
    }
}
