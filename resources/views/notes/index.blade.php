@extends('layouts.app')
@section('title', 'Notes')
@section('content')
<h2>Semua Notes</h2>

<form method="POST" action="/notes" class="card card-body mb-4">
    @csrf
    <h5>Buat Note Baru</h5>
    <div class="mb-3">
        <input type="text" name="title" class="form-control" placeholder="Judul" required>
    </div>
    <div class="mb-3">
        <textarea name="content" class="form-control" rows="3" placeholder="Isi note..." required></textarea>
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" name="is_private" class="form-check-input" value="1" checked>
        <label class="form-check-label">Private</label>
    </div>
    <button type="submit" class="btn btn-primary">Simpan Note</button>
</form>

{{-- VULNERABLE A01: Shows ALL notes, not just current user's --}}
@foreach($notes as $note)
    <div class="card mb-2">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h5><a href="/notes/{{ $note->id }}">{{ $note->title }}</a></h5>
                <form action="/notes/{{ $note->id }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </div>
            <small class="text-muted">Milik: {{ $note->user->name }} | {{ $note->is_private ? '🔒 Private' : '🌐 Public' }}</small>
        </div>
    </div>
@endforeach
@endsection
