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

    // Delete old codes
    LoginCode::where('email', $request->email)->delete();

    $code = LoginCode::generateCode();

    LoginCode::create([
        'email' => $request->email,
        'code' => $code,
        'expires_at' => now()->addMinutes(10),
        'used' => false,
    ]);

    // Store email in session
    session([
        'verification_email' => $request->email
    ]);

    // Send email
    Mail::to($request->email)->send(
        new LoginCodeMail($code)
    );

    // Redirect to verify page
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

    $loginCode = LoginCode::where('email', $email)
        ->where('used', 0)
        ->latest('id')
        ->first();

    if (!$loginCode) {
        return back()->withErrors([
            'code' => 'No active verification code found.'
        ]);
    }

    if (trim($request->code) !== trim($loginCode->code)) {
        return back()->withErrors([
            'code' => 'Incorrect verification code.'
        ]);
    }

    if (\Carbon\Carbon::parse($loginCode->expires_at)->isPast()) {
        return back()->withErrors([
            'code' => 'Code expired.'
        ]);
    }

    $loginCode->update([
        'used' => 1
    ]);

    $user = User::where('email', $email)->first();

    Auth::login($user);

    session()->forget('verification_email');

    $request->session()->regenerate();

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