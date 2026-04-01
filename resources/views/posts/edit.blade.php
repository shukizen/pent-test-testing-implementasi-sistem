@extends('layouts.app')
@section('title', 'Edit Post')
@section('content')
<h2>Edit Post</h2>
<form method="POST" action="/posts/{{ $post->id }}">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label>Judul</label>
        <input type="text" name="title" class="form-control" value="{{ $post->title }}" required>
    </div>
    <div class="mb-3">
        <label>Isi</label>
        <textarea name="body" class="form-control" rows="10" required>{{ $post->body }}</textarea>
    </div>
    <div class="form-check mb-3">
        <input type="checkbox" name="is_published" class="form-check-input" value="1" {{ $post->is_published ? 'checked' : '' }}>
        <label class="form-check-label">Published</label>
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
</form>
@endsection
