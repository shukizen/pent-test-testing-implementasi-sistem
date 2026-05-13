@extends('layouts.app')
@section('title', $user->name)
@section('content')
<div class="card">
    <div class="card-header"><h3>Profil: {{ $user->name }}</h3></div>
    <div class="card-body">
        <table class="table">
            <tr><th>Nama</th><td>{{ $user->name }}</td></tr>
            <tr><th>Email</th><td>{{ $user->email }}</td></tr>
            <tr><th>Role</th><td>{{ $user->role }}</td></tr>
            <tr><th>Phone</th><td>{{ $user->maskPhone($user->phone) ?? 'N/A' }}</td></tr>
            {{-- ✅ FIX A02: Masking data sensitif di halaman profil --}}
            <tr><th>NIK/SSN</th><td>{{ $user->maskSsn($user->ssn) ?? 'N/A' }}</td></tr>
            <tr><th>Bio</th><td>{!! $user->bio !!}</td></tr>
        </table>

        @auth
            <div class="mt-3">
                <a href="/profile/{{ $user->id }}/export" class="btn btn-info">Export Data (JSON)</a>
                <a href="/profile/{{ $user->id }}/api-keys" class="btn btn-secondary" target="_blank">Lihat API Keys</a>
            </div>
        @endauth
    </div>
</div>
@endsection
