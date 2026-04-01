@extends('layouts.app')
@section('title', 'Buat Post Baru')
@section('content')
<h2>Buat Post Baru</h2>
<form method="POST" action="/posts">
    @csrf
    <div class="mb-3">
        <label>Judul</label>
        <input type="text" name="title" class="form-control" required>
    </div>
    <div class="mb-3">
        <label>Isi (mendukung HTML)</label>
        {{-- VULNERABLE A03: Explicitly telling users they can use HTML --}}
        <textarea name="body" class="form-control" rows="10" required></textarea>
        <small class="text-muted">Kamu bisa menggunakan tag HTML untuk formatting.</small>
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" name="is_published" class="form-check-input" value="1">
        <label class="form-check-label">Publish langsung</label>
    </div>
    <button type="submit" class="btn btn-primary">Simpan</button>
</form>
@endsection
