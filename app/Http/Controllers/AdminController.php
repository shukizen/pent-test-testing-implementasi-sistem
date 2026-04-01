<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // VULNERABLE A01: No middleware to check admin role
    // Anyone authenticated can access admin dashboard
    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'posts' => Post::count(),
            'notes' => Note::count(),
        ];
        $users = User::all();

        return view('admin.dashboard', compact('stats', 'users'));
    }

    // VULNERABLE A01: No admin check + VULNERABLE A03: SQL injection
    public function searchUsers(Request $request)
    {
        $search = $request->input('q');
        // VULNERABLE A03: SQL injection in raw query
        $users = DB::select("SELECT * FROM users WHERE name LIKE '%" . $search . "%' OR email LIKE '%" . $search . "%'");

        return view('admin.dashboard', [
            'users' => $users,
            'stats' => ['users' => User::count(), 'posts' => Post::count(), 'notes' => Note::count()],
            'search' => $search,
        ]);
    }

    // VULNERABLE A01: No admin check - any user can delete other users
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect('/admin/dashboard')->with('success', 'User berhasil dihapus!');
    }

    // VULNERABLE A01: No admin check - any user can change roles
    public function updateUserRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->role = $request->input('role');
        $user->save();

        return redirect('/admin/dashboard')->with('success', 'Role berhasil diupdate!');
    }

    // VULNERABLE A05: Exposes system information
    public function systemInfo()
    {
        return response()->json([
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'CLI',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? base_path(),
            'database' => config('database.default'),
            'db_host' => config('database.connections.' . config('database.default') . '.host'),
            'db_name' => config('database.connections.' . config('database.default') . '.database'),
            'app_debug' => config('app.debug'),
            'app_env' => config('app.env'),
            'app_key' => config('app.key'), // VULNERABLE A05: Exposing APP_KEY
        ]);
    }
}
