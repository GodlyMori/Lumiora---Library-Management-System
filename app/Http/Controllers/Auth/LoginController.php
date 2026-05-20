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
    /**
     * Show the login form (email only)
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Request a verification code to be sent to email
     */
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

    // ❗ IMPORTANT FIX: invalidate ALL previous codes
    LoginCode::where('email', $request->email)
        ->update(['used' => 1]);

    // Generate new code
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
            ->with('success', 'Verification code sent to your email!');
    } catch (\Exception $e) {
        return back()->withErrors(['email' => 'Failed to send email. Please try again.'])
                    ->onlyInput('email');
    }
}

    /**
     * Show the code verification form
     */
    public function showVerifyForm()
    {
        if (!session('verification_email')) {
            return redirect()->route('login')
                           ->withErrors(['email' => 'Please request a verification code first.']);
        }

        return view('auth.verify-code');
    }

    /**
     * Verify the code and log in the user
     */
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

    // ❗ STRICT FIX: only ONE valid code allowed
    $loginCode = LoginCode::where('email', $email)
        ->where('used', 0)
        ->where('expires_at', '>=', now())
        ->latest()
        ->first();

    if (!$loginCode) {
        return back()->withErrors(['code' => 'Code expired. Please request a new one.']);
    }

    // ❗ STRING SAFE COMPARISON
    if ((string) $loginCode->code !== (string) $request->code) {
        return back()->withErrors(['code' => 'Invalid verification code.']);
    }

    // Mark as used
    $loginCode->update(['used' => 1]);

    $user = User::where('email', $email)->first();

    Auth::login($user, $request->boolean('remember'));

    session()->forget('verification_email');

    $request->session()->regenerate();

    return redirect()->intended(route('dashboard'))
        ->with('success', 'Welcome back, ' . $user->name . '!');
}

    /**
     * Resend verification code
     */
    public function resendCode(Request $request)
{
    $email = session('verification_email');

    if (!$email) {
        return redirect()->route('login')
            ->withErrors(['email' => 'Session expired. Please start again.']);
    }

    $user = User::where('email', $email)->first();

    // ❗ FIX: invalidate old codes before resend
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

        return back()->with('success', 'New verification code sent!');
    } catch (\Exception $e) {
        return back()->withErrors(['code' => 'Failed to send email. Please try again.']);
    }
}

    /**
     * Logout the user
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
