<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    // VULNERABLE A01: IDOR - Shows notes based on ID without ownership check
    public function index()
    {
        // Should filter by Auth::id() but shows all notes
        $notes = Note::with('user')->get();
        return view('notes.index', compact('notes'));
    }

    // VULNERABLE A01: IDOR - Any authenticated user can view any note by changing the ID
    public function show($id)
    {
        $note = Note::findOrFail($id);
        // VULNERABLE: No check if note belongs to current user
        return view('notes.show', compact('note'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        Note::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'content' => $request->content,
            'is_private' => $request->has('is_private'),
        ]);

        return redirect('/notes')->with('success', 'Note berhasil dibuat!');
    }

    // VULNERABLE A01: IDOR - Any user can delete any note
    public function destroy($id)
    {
        $note = Note::findOrFail($id);
        // VULNERABLE: No ownership check
        $note->delete();

        return redirect('/notes')->with('success', 'Note berhasil dihapus!');
    }
}
