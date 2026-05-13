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
        // VULNERABLE A02: Exposes all user data including SSN
        $users = User::all();
        return response()->json($users->makeVisible(['ssn', 'phone']));
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

    // VULNERABLE A08: Accepts serialized data via API
    public function processWebhook(Request $request)
    {
        $payload = $request->input('payload');

        // VULNERABLE A08: Deserializing untrusted data
        if ($payload) {
            $data = unserialize(base64_decode($payload));
            return response()->json(['processed' => $data]);
        }

        return response()->json(['error' => 'Payload diperlukan'], 400);
    }
}
