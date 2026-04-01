<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    // VULNERABLE A07: No rate limiting, no brute force protection
    // VULNERABLE A07: No password complexity requirements
    // VULNERABLE A09: No logging of failed login attempts
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            // VULNERABLE A09: No logging of successful login
            return redirect()->intended('/dashboard');
        }

        // VULNERABLE A07: Reveals whether email exists
        $user = User::where('email', $request->email)->first();
        if ($user) {
            return back()->withErrors(['password' => 'Password salah untuk akun ini.']);
        }

        return back()->withErrors(['email' => 'Email tidak ditemukan di sistem kami.']);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    // VULNERABLE A07: No password strength validation
    // VULNERABLE A01: Role can be set via mass assignment
    // VULNERABLE A04: No CAPTCHA
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:1', // VULNERABLE: min 1 char password
        ]);

        // VULNERABLE A01: Mass assignment - user can send role=admin
        $user = User::create($request->all());

        Auth::login($user);

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    // VULNERABLE A02: Password reset token is predictable (MD5 of email + time)
    // VULNERABLE A04: No email verification, token sent in response directly
    public function forgotPassword(Request $request)
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        // VULNERABLE A02: Predictable token using MD5
        $token = md5($email . date('Y-m-d'));

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $token, 'created_at' => now()]
        );

        // VULNERABLE A04: Token exposed in response (should be sent via email only)
        return response()->json([
            'message' => 'Token reset password berhasil dibuat',
            'token' => $token,
            'reset_url' => url("/reset-password?token={$token}&email={$email}")
        ]);
    }

    public function resetPassword(Request $request)
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Token tidak valid'], 400);
        }

        // VULNERABLE A04: Token never expires
        $user = User::where('email', $request->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password berhasil direset']);
    }
}
