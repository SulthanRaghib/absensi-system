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
            $today = Carbon::today();

            // 1. STRICT IP CHECK: Check if this IP is already used by ANOTHER user today
            // "One IP per User"
            $otherUserUsingIp = LoginHistory::where('ip_address', $ip)
                ->whereDate('login_at', $today)
                ->where('user_id', '!=', $user->id)
                ->first();

            if ($otherUserUsingIp) {
                $this->handleSuspiciousLogin($request, $user, $ip, $otherUserUsingIp->user_id, 'IP Address conflict');
                return $this->sendFraudResponse($request);
            }

            // 2. STRICT USER CHECK: Check if this User has already used ANOTHER IP today
            // "One User per IP" (Prevents user from hopping IPs/Devices)
            $userUsedOtherIp = LoginHistory::where('user_id', $user->id)
                ->where('ip_address', '!=', $ip)
                ->whereDate('login_at', $today)
                ->first();

            if ($userUsedOtherIp) {
                // We can treat this as fraud or just block it.
                // For strict "One Device" policy, we block it.
                $this->handleSuspiciousLogin($request, $user, $ip, $user->id, 'User IP hopping');
                return $this->sendFraudResponse($request);
            }

            // 3. Safe to Login - Update or Create History
            LoginHistory::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'login_at' => $today, // Keying by date ensures one record per day per user
                ],
                [
                    'ip_address' => $ip,
                    'user_agent' => $request->userAgent(),
                    'login_at' => now(), // Update timestamp
                ]
            );

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

    protected function handleSuspiciousLogin($request, $user, $ip, $previousUserId, $reason = 'Unknown')
    {
        SuspiciousLogin::create([
            'attempted_email' => $user->email,
            'ip_address' => $ip,
            'previous_user_id' => $previousUserId,
            'blocked_at' => now(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    protected function sendFraudResponse($request)
    {
        $message = 'Hayolohhh mau titip absen siapaaa?, gw laporin lohhh 📸';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => ['fraud_alert' => [$message]],
                'csrf_token' => csrf_token(),
            ], 422);
        }

        return back()->withErrors([
            'fraud_alert' => $message,
        ])->onlyInput('email');
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
