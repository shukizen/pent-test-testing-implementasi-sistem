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

        // VULNERABLE A02: Weak key generation using md5 + predictable values
        $key = md5($user->email . time());
        $secret = md5($user->id . date('Y-m-d'));

        $apiKey = ApiKey::create([
            'user_id' => $user->id,
            'key' => $key,
            'secret' => $secret, // VULNERABLE A02: Stored as plaintext
        ]);

        return response()->json([
            'key' => $apiKey->key,
            'secret' => $apiKey->secret, // VULNERABLE A02: Secret exposed in response
        ]);
    }

    // VULNERABLE A01: Any user can view any user's API keys
    public function apiKeys($userId)
    {
        $keys = ApiKey::where('user_id', $userId)->get();
        return response()->json($keys); // VULNERABLE A02: Secrets exposed
    }

    // VULNERABLE A02: Export user data without encryption
    public function exportData($id)
    {
        $user = User::findOrFail($id);
        // VULNERABLE A01: No ownership check
        // VULNERABLE A02: Sensitive data (SSN, phone) exported in plaintext
        return response()->json([
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'ssn' => $user->ssn,
            'role' => $user->role,
            'bio' => $user->bio,
        ]);
    }
}
