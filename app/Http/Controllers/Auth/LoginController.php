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

        // Check if user exists
        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return back()->withErrors(['email' => 'No account found with this email address.'])
                        ->onlyInput('email');
        }

        // Clean up old codes
        LoginCode::cleanUp();

        // Generate a new 6-digit code
        $code = LoginCode::generateCode();

        // Store the code in database
        LoginCode::create([
            'email' => $request->email,
            'code' => $code,
            'expires_at' => now()->addMinutes(10), // Code valid for 10 minutes
            'used' => false,
        ]);

        // Send the code via email
try {
    Mail::to($request->email)->send(new LoginCodeMail($code, $user->name));
    
    // Store email in session for verification step
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

        // Find the most recent unused code for this email
        $loginCode = LoginCode::where('email', $email)
                              ->where('used', false)
                              ->where('expires_at', '>', now())
                              ->orderBy('created_at', 'desc')
                              ->first();

        if (!$loginCode || $loginCode->code !== $request->code) {
            return back()->withErrors(['code' => 'Invalid or expired verification code.']);
        }

        // Mark code as used
        $loginCode->markAsUsed();

        // Log in the user
        $user = User::where('email', $email)->first();
        Auth::login($user, $request->boolean('remember'));

        // Clear verification email from session
        session()->forget('verification_email');
        
        // Regenerate session
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

        // Generate a new code
        $code = LoginCode::generateCode();

        // Store the new code
        LoginCode::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
            'used' => false,
        ]);

        // Send the new code
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
