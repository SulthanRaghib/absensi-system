<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\LoginHistory;
use App\Models\SuspiciousLogin;
use Carbon\Carbon;
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

        return view('auth.choice', compact('officeLocation'));
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
            $user = Auth::user();
            $ip = $request->ip();

            // Check if this IP was used by another user today
            $previousLogin = LoginHistory::where('ip_address', $ip)
                ->whereDate('login_at', Carbon::today())
                ->where('user_id', '!=', $user->id)
                ->first();

            if ($previousLogin) {
                // Log suspicious activity
                SuspiciousLogin::create([
                    'attempted_email' => $user->email,
                    'ip_address' => $ip,
                    'previous_user_id' => $previousLogin->user_id,
                    'blocked_at' => now(),
                ]);

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = 'Hayolohhh mau titip absen siapaaa?, gw laporin lohhh 📸';

                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $message,
                        'errors' => ['fraud_alert' => [$message]],
                        'csrf_token' => csrf_token(), // Send new token
                    ], 422);
                }

                return back()->withErrors([
                    'fraud_alert' => $message,
                ])->onlyInput('email');
            }

            // Update or Create Login History
            $existingHistory = LoginHistory::where('user_id', $user->id)
                ->where('ip_address', $ip)
                ->whereDate('login_at', Carbon::today())
                ->first();

            if ($existingHistory) {
                $existingHistory->update([
                    'login_at' => now(),
                    'user_agent' => $request->userAgent(),
                ]);
            } else {
                LoginHistory::create([
                    'user_id' => $user->id,
                    'ip_address' => $ip,
                    'user_agent' => $request->userAgent(),
                    'login_at' => now(),
                ]);
            }

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
