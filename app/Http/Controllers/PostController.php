<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mews\Purifier\Facades\Purifier;

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

        // ✅ FIX: Gunakan Eloquent dengan parameter binding dan query grouping
        $posts = Post::with('user')
            ->where(function ($query) use ($keyword) {
                $query->where('title', 'LIKE', '%' . $keyword . '%')
                      ->orWhere('body', 'LIKE', '%' . $keyword . '%');
            })
            ->where('is_published', true)
            ->get();

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
            'title' => strip_tags($request->title),
            'body' => Purifier::clean($request->body), // ✅ Sanitize HTML input
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

        $post->update([
            'title' => strip_tags($request->title),
            'body' => Purifier::clean($request->body), // ✅ Sanitize HTML input
            'is_published' => $request->has('is_published'),
        ]);
        return redirect("/posts/{$id}");
    }

    // VULNERABLE A01: No authorization check - any user can delete any post
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        // ✅ FIX: Cek kepemilikan post
        if ($post->user_id !== Auth::id()) {
            abort(403, 'Anda tidak berhak menghapus post ini.');
        }

        $post->delete();

        return redirect('/posts')->with('success', 'Post berhasil dihapus!');
    }
}
