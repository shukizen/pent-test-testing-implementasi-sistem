@extends('layouts.app')
@section('title', $post->title)
@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $post->title }}</h3>
        <small>Oleh: {{ $post->user->name }} | {{ $post->created_at->diffForHumans() }}</small>
    </div>
    <div class="card-body">
        {{-- ✅ FIX: Render HTML yang sudah disanitasi dengan clean() --}}
        <div>{!! clean($post->body) !!}</div>
    </div>
</div>
<a href="/posts" class="btn btn-secondary mt-3">Kembali</a>
@endsection
