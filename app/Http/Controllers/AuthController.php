<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use PragmaRX\Google2FA\Google2FA;
use App\Services\SecurityLogger;

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
        // ✅ FIX: Validasi captcha
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'g-recaptcha-response' => 'required|captcha',
        ]);

        // ✅ FIX: Account Lockout Check
        $key = 'login_attempts_' . $request->ip() . '_' . $request->email;

        if (Cache::get($key, 0) >= 5) {
            $minutes = Cache::get($key . '_lockout', 15);
            return back()->withErrors([
                'email' => "Akun terkunci. Coba lagi dalam {$minutes} menit."
            ]);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            // ✅ FIX: Reset attempts on successful login
            Cache::forget($key);
            Cache::forget($key . '_lockout');

            // ✅ FIX: Jika 2FA aktif, redirect ke verifikasi 2FA, bukan langsung ke dashboard
            // $user = Auth::user();
            // if ($user->google2fa_secret) {
            //     return redirect()->route('2fa.verify');
            // }

            // ✅ FIX A09: Log login berhasil ke Security Log
            SecurityLogger::authSuccess(Auth::user(), $request->ip());

            return redirect()->intended('/dashboard');
        }


        // ✅ FIX A09: Log login gagal ke Security Log
        SecurityLogger::authFailed($request->input('email'), $request->ip());

        // ✅ FIX: Increment failed attempts and set lockout duration
        $attempts = Cache::increment($key);
        if ($attempts == 1) {
            Cache::put($key, 1, now()->addMinutes(15));
        }
        Cache::put($key . '_lockout', 15, now()->addMinutes(15));

        $remaining = 5 - Cache::get($key, 0);

        // ✅ FIX: Pesan error yang SAMA untuk semua kasus (Mencegah Username Enumeration)
        // ✅ FIX: Pesan error yang SAMA untuk semua kasus
        return back()->withErrors([
            'email' => 'Email atau password tidak valid.',
        ]);
        // Jangan bedakan antara "email tidak ada" dan "password salah"
        // return back()->withErrors(['email' => "Email tidak ditemukan di sistem kami. Sisa percobaan: {$remaining}"]);
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
            'g-recaptcha-response' => 'required|captcha', // ✅ FIX: Validasi captcha
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed', // Butuh field password_confirmation
                'regex:/[a-z]/',      // Minimal 1 huruf kecil
                'regex:/[A-Z]/',      // Minimal 1 huruf besar
                'regex:/[0-9]/',      // Minimal 1 angka
                'regex:/[@$!%*#?&]/', // Minimal 1 special character
            ],
        ], [
            'password.min' => 'Password minimal 8 karakter.',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan karakter spesial.',
        ]);

        // VULNERABLE A01: Mass assignment - user can send role=admin
        // ✅ FIX: Jangan gunakan $request->all() - tentukan field spesifik
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'user', // Hardcode role
        ]);

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

        // ✅ FIX: Jangan beritahu apakah email terdaftar atau tidak
        // (selalu return response yang sama)

        if ($user) {
            // ✅ FIX: Gunakan token random yang kuat
            $token = hash('sha256', Str::random(60));

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                ['token' => bcrypt($token), 'created_at' => now()]
            );

            // ✅ FIX: Kirim token via email, JANGAN return di response
            // Mail::to($email)->send(new ResetPasswordMail($token));
        }

        return response()->json([
            'message' => 'Jika email terdaftar, link reset password akan dikirim.'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json(['message' => 'Token tidak valid'], 400);
        }

        // ✅ FIX: Check expiry (60 menit)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'Token sudah expired'], 400);
        }
        if (!Hash::check($request->token, $record->token)) {
            return response()->json(['message' => 'Token tidak valid'], 400);
        }
        $user = User::where('email', $request->email)->first();
        $user->password = $request->password;
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password berhasil direset']);
    }

    public function enable2FA(Request $request)
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $user = Auth::user();
        $user->google2fa_secret = $secret;
        $user->save();

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return view('auth.2fa-setup', compact('qrCodeUrl', 'secret'));
    }

    public function show2FAVerify()
    {
        return view('auth.2fa-verify');
    }

    public function verify2FA(Request $request)
    {
        $request->validate([
            'one_time_password' => 'required|string|size:6',
        ]);

        $google2fa = new Google2FA();
        $user = Auth::user();

        // Verifikasi OTP yang diinput dengan Kunci Rahasia di database
        $valid = $google2fa->verifyKey($user->google2fa_secret, $request->one_time_password);

        if ($valid) {
            // Tandai di session bahwa user berhasil melewati tantangan 2FA
            $request->session()->put('2fa_verified', true);
            return redirect()->intended('/dashboard');
        }

        return back()->with('error', 'Kode OTP salah atau sudah kedaluwarsa.');
    }
}
