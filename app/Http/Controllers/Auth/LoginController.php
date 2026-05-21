<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginCode;
use App\Models\User;
use App\Mail\LoginCodeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function requestCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'No account found with this email address.'
            ])->onlyInput('email');
        }

        // remove old codes
        LoginCode::where('email', $request->email)->delete();

        $code = LoginCode::generateCode();

        $loginCode = LoginCode::create([
            'email' => $request->email,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'used' => 0,
        ]);

        // store session (IMPORTANT)
        session([
            'verification_email' => $request->email
        ]);

        // send email (make sure this works in prod)
        Mail::to($request->email)->send(new LoginCodeMail($code));

        return redirect()->route('login.verify.show')
            ->with('success', 'Verification code sent to your email.');
    }

    public function showVerifyForm()
    {
        if (!session('verification_email')) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Please request a code first.']);
        }

        return view('auth.verify-code');
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $email = session('verification_email');

        if (!$email) {
            return redirect()->route('login')
                ->withErrors([
                    'email' => 'Session expired. Please request a new code.'
                ]);
        }

        // Fetch the latest unused code for this email (don't rely on DB time comparison)
        // We'll perform the expiration check in PHP using the model cast (timezone-safe).
        $loginCode = LoginCode::where('email', $email)
            ->where('used', false)
            ->latest()
            ->first();

        if (!$loginCode) {
            return back()->withErrors([
                'code' => 'No verification code found.'
            ]);
        }

        // DEBUG: uncomment if needed
        
        dd([
            'now' => now(),
            'expires_at' => $loginCode->expires_at,
            'diff' => now()->diffInSeconds($loginCode->expires_at, false),
            'code_db' => $loginCode->code,
            'code_input' => $request->code,
        ]);
        

        // SAFE expiration check using model cast helper
        if (now()->gt($loginCode->expires_at)) {
    return back()->withErrors([
        'code' => 'Code expired.'
    ]);
}

        if (trim($request->code) !== trim($loginCode->code)) {
            return back()->withErrors([
                'code' => 'Incorrect verification code.'
            ]);
        }

        if ($loginCode->used) {
            return back()->withErrors([
                'code' => 'Code already used.'
            ]);
        }

        $loginCode->update([
            'used' => 1
        ]);

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('login')
                ->withErrors([
                    'email' => 'User not found.'
                ]);
        }

        Auth::login($user);

        session()->forget('verification_email');

        request()->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}