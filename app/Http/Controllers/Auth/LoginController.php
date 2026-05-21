<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

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
                'email' => 'No account found.'
            ]);
        }

        // generate OTP
        $code = random_int(100000, 999999);

        // store OTP
        LoginCode::create([
            'email' => $request->email,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'used' => false,
        ]);

        // send via Resend API
        Http::withHeaders([
            'Authorization' => 'Bearer ' . env('RESEND_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.resend.com/emails', [
            'from' => 'Your App <onboarding@resend.dev>',
            'to' => $request->email,
            'subject' => 'Your Login Code',
            'html' => "<h2>Your OTP Code is: <strong>{$code}</strong></h2>",
        ]);

        // ONLY session needed
        session(['otp_email' => $request->email]);

        return redirect()->route('login.verify.show');
    }

    public function showVerifyForm()
    {
        if (!session('otp_email')) {
            return redirect()->route('login');
        }

        return view('auth.verify-code');
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $email = session('otp_email');

        if (!$email) {
            return redirect()->route('login');
        }

        // get latest OTP
        $otp = LoginCode::where('email', $email)
            ->latest('id')
            ->first();

        if (!$otp) {
            return back()->withErrors(['code' => 'No OTP found.']);
        }

        if ($otp->used) {
            return back()->withErrors(['code' => 'OTP already used.']);
        }

        if (now()->gt($otp->expires_at)) {
            return back()->withErrors(['code' => 'OTP expired.']);
        }

        if ($request->code !== $otp->code) {
            return back()->withErrors(['code' => 'Incorrect code.']);
        }

        // mark used
        $otp->update(['used' => true]);

        // login user
        $user = User::where('email', $email)->first();

        Auth::login($user);

        session()->forget('otp_email');

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}