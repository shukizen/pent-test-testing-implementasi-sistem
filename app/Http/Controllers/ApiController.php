<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ApiController extends Controller
{
    // VULNERABLE A01: API endpoint without authentication
    public function listUsers()
    {
        // ✅ FIX A02: Masking data sensitif di respon API
        $users = User::all()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->maskPhone($user->phone),
                'ssn' => $user->maskSsn($user->ssn),
                'bio' => $user->bio,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });
        return response()->json($users);
    }

    // VULNERABLE A03: SQL injection via API
    public function searchPosts(Request $request)
    {
        $title = $request->input('title');
        // VULNERABLE A03: Raw SQL with user input
        $posts = DB::select("SELECT * FROM posts WHERE title LIKE '%" . $title . "%'");
        return response()->json($posts);
    }

    // VULNERABLE A07: API key auth with timing attack vulnerability
    public function authenticatedEndpoint(Request $request)
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            return response()->json(['error' => 'API Key diperlukan'], 401);
        }

        // VULNERABLE A07: String comparison vulnerable to timing attacks
        $validKey = DB::table('api_keys')->where('key', $apiKey)->first();

        if (!$validKey) {
            return response()->json(['error' => 'API Key tidak valid'], 401);
        }

        return response()->json([
            'message' => 'Authenticated!',
            'user_id' => $validKey->user_id,
        ]);
    }

    // VULNERABLE A09: No logging, no rate limiting on sensitive operations
    public function bulkDeletePosts(Request $request)
    {
        $ids = $request->input('ids', []);

        // ✅ FIX A01: Pengecekan kepemilikan pada bulk delete
        // Hanya hapus post yang merupakan milik user yang sedang login
        $deletedCount = Post::whereIn('id', $ids)
            ->where('user_id', Auth::id())
            ->delete();

        return response()->json(['message' => $deletedCount . ' posts berhasil dihapus']);
    }


    public function processWebhook(Request $request)
    {
        // ✅ FIX: Verifikasi signature
        $signature = $request->header('X-Webhook-Signature');
        
        // ✅ Tambahan Keamanan: Cegah TypeError di PHP 8.3 jika header kosong
        if (!$signature) {
            return response()->json(['error' => 'Signature tidak valid'], 401);
        }

        $payload = $request->getContent();
        // ✅ Fallback ke default secret sesuai dokumentasi jika belum didefinisikan di config
        $secret = config('services.webhook.secret', 'your-webhook-secret');

        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            return response()->json(['error' => 'Signature tidak valid'], 401);
        }

        // ✅ FIX: Gunakan JSON decode, bukan unserialize
        $data = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
             return response()->json(['error' => 'Format JSON tidak valid'], 400);
        }

        // ✅ FIX: Validasi struktur data
        try {
            $validated = validator($data, [
                'event' => 'required|string|in:order.created,payment.received',
                'data' => 'required|array',
            ])->validate();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Struktur data tidak valid'], 400);
        }

        return response()->json(['processed' => $validated]);
    }
}
