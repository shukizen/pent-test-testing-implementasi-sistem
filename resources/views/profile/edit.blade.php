@extends('layouts.app')
@section('title', 'Edit Profil')
@section('content')
<h2>Edit Profil</h2>
<form method="POST" action="/profile/update">
    @csrf
    @method('PUT')
    <div class="mb-3">
        <label>Nama</label>
        <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
    </div>
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
    </div>
    <div class="mb-3">
        <label>Phone</label>
        <input type="text" name="phone" class="form-control" value="{{ $user->phone }}">
    </div>
    <div class="mb-3">
        <label>NIK/SSN</label>
        <input type="text" name="ssn" class="form-control" value="{{ $user->ssn }}">
    </div>
    <div class="mb-3">
        <label>Bio</label>
        <textarea name="bio" class="form-control" rows="3">{{ $user->bio }}</textarea>
    </div>
    {{-- VULNERABLE A01: Hidden role field - can be modified via DevTools --}}
    <input type="hidden" name="role" value="{{ $user->role }}">
    <button type="submit" class="btn btn-primary">Update Profil</button>
    <a href="/profile/api-key" class="btn btn-secondary" onclick="event.preventDefault(); fetch('/profile/api-key', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json'}}).then(r=>r.json()).then(d=>alert('Key: '+d.key+'\nSecret: '+d.secret))">Generate API Key</a>
</form>
@endsection
