<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Auth;

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

        // ✅ FIX A09: Log user deletion audit
        SecurityLogger::userDeleted(Auth::id(), $user->id, $user->email);

        $user->delete();

        return redirect('/admin/dashboard')->with('success', 'User berhasil dihapus!');
    }

    // VULNERABLE A01: No admin check - any user can change roles
    public function updateUserRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $oldRole = $user->role;

        $user->role = $request->input('role');
        $user->save();

        // ✅ FIX A09: Log user privilege role change
        SecurityLogger::privilegeChange(Auth::id(), $user->id, $oldRole, $user->role);

        return redirect('/admin/dashboard')->with('success', 'Role berhasil diupdate!');
    }

    // ✅ FIX: Lindungi endpoint info sistem dan hilangkan info sensitif
    public function systemInfo()
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        return response()->json([
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'CLI',
            // ✅ SENSITIVE INFO REMOVED: app_key, db_name, database, document_root, dsb.
        ]);
    }
}
