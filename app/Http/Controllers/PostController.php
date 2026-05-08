<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')->where('is_published', true)->get();
        return view('posts.index', compact('posts'));
    }

    // VULNERABLE A03: SQL Injection via raw query
    public function search(Request $request)
    {
        $keyword = $request->input('q');

        // VULNERABLE A03: Direct string concatenation in raw SQL
        $posts = DB::select("SELECT posts.*, users.name as author_name FROM posts JOIN users ON posts.user_id = users.id WHERE posts.title LIKE '%" . $keyword . "%' OR posts.body LIKE '%" . $keyword . "%'");

        return view('posts.index', ['posts' => $posts, 'keyword' => $keyword]);
    }

    public function show($id)
    {
        $post = Post::with('user')->findOrFail($id);
        return view('posts.show', compact('post'));
    }

    public function create()
    {
        return view('posts.create');
    }

    // VULNERABLE A03: XSS - body is stored without sanitization and rendered with {!! !!}
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        Post::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'body' => $request->body, // VULNERABLE: No XSS sanitization
            'is_published' => $request->has('is_published'),
        ]);

        return redirect('/posts')->with('success', 'Post berhasil dibuat!');
    }

    // VULNERABLE A01: No authorization check - any user can edit any post
    public function edit($id)
    {
        $post = Post::findOrFail($id);
        // ✅ FIX: Cek kepemilikan post
        if ($post->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak mengedit post ini.');
        }
        return view('posts.edit', compact('post'));
    }

    // VULNERABLE A01: No authorization check - any user can update any post
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        // ✅ FIX: Cek kepemilikan post
        if ($post->user_id !== Auth::id()) {
            abort(403, 'Anda tidak bisa mengedit post ini.');
        }

        $post->update($request->only('title', 'body', 'is_published'));
        return redirect("/posts/{$id}");
    }

    // VULNERABLE A01: No authorization check - any user can delete any post
    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        // VULNERABLE A01: Missing ownership check
        $post->delete();

        return redirect('/posts')->with('success', 'Post berhasil dihapus!');
    }
}
