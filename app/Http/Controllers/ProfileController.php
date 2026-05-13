<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function show($id)
    {
        $user = User::findOrFail($id);
        // VULNERABLE A02: SSN (sensitive data) exposed in response
        // VULNERABLE A01: Can view any user's profile including sensitive data
        return view('profile.show', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        // VULNERABLE A01: All fields including 'role' can be updated
        $user->update($request->only(['name', 'email', 'phone', 'bio']));

        return redirect("/profile/{$user->id}")->with('success', 'Profil berhasil diupdate!');
    }

    // VULNERABLE A02: API keys generated with weak randomness and stored in plaintext
    public function generateApiKey()
    {
        $user = Auth::user();

        // ✅ FIX: Gunakan random string yang kuat
        $key = 'pk_' . Str::random(40);
        $secret = 'sk_' . Str::random(40);

        $apiKey = ApiKey::create([
            'user_id' => $user->id,
            'key' => $key,
            // ✅ FIX: Hash secret sebelum disimpan
            'secret' => hash('sha256', $secret),
        ]);

        // Tampilkan secret hanya SEKALI saat pembuatan
        return response()->json([
            'key' => $key,
            'secret' => $secret,
            'message' => 'Simpan secret ini! Tidak akan ditampilkan lagi.',
        ]);
    }

    // VULNERABLE A01: Any user can view any user's API keys
    public function apiKeys($userId)
    {
        // ✅ FIX A01: Pengecekan kepemilikan API keys
        if ($userId != Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Akses ditolak. Anda tidak bisa melihat API Key milik user lain.');
        }

        $keys = ApiKey::where('user_id', $userId)->get();
        return response()->json($keys);
    }

    // VULNERABLE A02: Export user data without encryption
    public function exportData($id)
    {
        $user = User::findOrFail($id);
        
        // ✅ FIX A01: Pengecekan kepemilikan ekspor data
        if ($id != Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Akses ditolak. Anda tidak berhak mengekspor data user lain.');
        }

        // VULNERABLE A02: Sensitive data (SSN, phone) exported in plaintext
        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->maskPhone($user->phone),
            'ssn' => $user->maskSsn($user->ssn),
            'role' => $user->role,
            'bio' => $user->bio,
        ]);
    }
}
