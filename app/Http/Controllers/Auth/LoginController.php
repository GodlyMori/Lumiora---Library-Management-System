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
            return back()->withErrors(['email' => 'No account found with this email address.'])
                ->onlyInput('email');
        }

        // 🔥 INVALDATE ALL OLD CODES (CRITICAL FIX)
        LoginCode::where('email', $request->email)
            ->update(['used' => 1]);

        // Generate OTP
        $code = LoginCode::generateCode();

        LoginCode::create([
            'email' => $request->email,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'used' => 0,
        ]);

        try {
            Mail::to($request->email)->send(new LoginCodeMail($code, $user->name));

            session(['verification_email' => $request->email]);

            return redirect()->route('login.verify.show')
                ->with('success', 'Verification code sent!');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Failed to send email.']);
        }
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
            ->withErrors(['email' => 'Session expired. Please request a new code.']);
    }

    $loginCode = LoginCode::where('email', $request->email)
    ->where('code', $request->code)
    ->first();

if (!$loginCode) {
    return back()->with('error', 'Invalid verification code.');
}

if (\Carbon\Carbon::parse($loginCode->expires_at)->isPast()) {
    return back()->with('error', 'Code expired.');
}

    // ✅ FIXED UTC COMPARISON
    if (now()->greaterThan($loginCode->expires_at)) {
        return back()->withErrors(['code' => 'Code expired.']);
    }

    if ((int)$loginCode->used === 1) {
        return back()->withErrors(['code' => 'Code already used.']);
    }

    $loginCode->update(['used' => 1]);

    $user = User::where('email', $email)->first();

    Auth::login($user);

    session()->forget('verification_email');

    $request->session()->regenerate();

    return redirect()->route('dashboard')
        ->with('success', 'Welcome back, ' . $user->name . '!');
}
    public function resendCode(Request $request)
    {
        $email = session('verification_email');

        if (!$email) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Session expired.']);
        }

        $user = User::where('email', $email)->first();

        // 🔥 invalidate old OTPs
        LoginCode::where('email', $email)
            ->update(['used' => 1]);

        $code = LoginCode::generateCode();

        LoginCode::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'used' => 0,
        ]);

        try {
            Mail::to($email)->send(new LoginCodeMail($code, $user->name));

            return back()->with('success', 'New code sent!');
        } catch (\Exception $e) {
            return back()->withErrors(['code' => 'Failed to send email.']);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}