@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<h2>Dashboard</h2>
<p>Selamat datang, <strong>{{ $user->name }}</strong>! (Role: {{ $user->role }})</p>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Post Saya ({{ $posts->count() }})</div>
            <div class="card-body">
                @foreach($posts as $post)
                    <p><a href="/posts/{{ $post->id }}">{{ $post->title }}</a> - {{ $post->is_published ? '✅ Published' : '📝 Draft' }}</p>
                @endforeach
                <a href="/posts/create/new" class="btn btn-sm btn-primary">Buat Post Baru</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Notes Saya ({{ $notes->count() }})</div>
            <div class="card-body">
                @foreach($notes as $note)
                    <p><a href="/notes/{{ $note->id }}">{{ $note->title }}</a></p>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div class="mt-4">
    <a href="/profile/edit/me" class="btn btn-outline-primary">Edit Profil</a>
    <a href="/profile/{{ $user->id }}" class="btn btn-outline-secondary">Lihat Profil</a>
</div>
@endsection
