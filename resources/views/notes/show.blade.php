@extends('layouts.app')
@section('title', $note->title)
@section('content')
<div class="card">
    <div class="card-header">
        <h3>{{ $note->title }}</h3>
        <small>Milik: {{ $note->user->name }} | {{ $note->is_private ? '🔒 Private' : '🌐 Public' }}</small>
    </div>
    <div class="card-body">
        {{-- VULNERABLE A01: Private notes accessible to anyone --}}
        <p>{{ $note->content }}</p>
    </div>
</div>
<a href="/notes" class="btn btn-secondary mt-3">Kembali</a>
@endsection
