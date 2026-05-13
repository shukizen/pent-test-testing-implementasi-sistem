@extends('layouts.app')
@section('title', 'Posts')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Posts</h2>
    @auth
        <a href="/posts/create/new" class="btn btn-primary">Buat Post Baru</a>
    @endauth
</div>

{{-- VULNERABLE A03: Search form susceptible to SQL injection --}}
<form action="/posts/search" method="GET" class="mb-4">
    <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Cari post..." value="{{ $keyword ?? '' }}">
        <button class="btn btn-outline-secondary" type="submit">Cari</button>
    </div>
</form>

@forelse($posts as $post)
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">
                <a href="/posts/{{ $post->id ?? $post->id }}">{{ $post->title }}</a>
            </h5>
            {{-- ✅ FIX: Render HTML yang disanitasi dengan clean() --}}
            <div class="card-text">{!! clean(Str::limit($post->body ?? $post->body, 200)) !!}</div>
            <small class="text-muted">
                Oleh: {{ $post->user->name ?? $post->author_name ?? 'Unknown' }}
            </small>
            @auth
                <div class="mt-2">
                    <a href="/posts/{{ $post->id }}/edit" class="btn btn-sm btn-warning">Edit</a>
                    <form action="/posts/{{ $post->id }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </div>
            @endauth
        </div>
    </div>
@empty
    <p>Tidak ada post.</p>
@endforelse
@endsection
