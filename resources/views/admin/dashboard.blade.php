@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
{{-- VULNERABLE A01: No server-side admin check, only client-side --}}
<h2>🛡️ Admin Dashboard</h2>

<div class="row mb-4">
    <div class="col-md-4"><div class="card bg-primary text-white p-3"><h4>{{ $stats['users'] }}</h4><p>Users</p></div></div>
    <div class="col-md-4"><div class="card bg-success text-white p-3"><h4>{{ $stats['posts'] }}</h4><p>Posts</p></div></div>
    <div class="col-md-4"><div class="card bg-info text-white p-3"><h4>{{ $stats['notes'] }}</h4><p>Notes</p></div></div>
</div>

<h4>Cari User</h4>
<form action="/admin/users/search" method="GET" class="mb-3">
    <div class="input-group">
        <input type="text" name="q" class="form-control" placeholder="Cari user..." value="{{ $search ?? '' }}">
        <button class="btn btn-outline-primary" type="submit">Cari</button>
    </div>
</form>

<h4>Daftar Users</h4>
<table class="table table-striped">
    <thead>
        <tr><th>ID</th><th>Nama</th><th>Email</th><th>Role</th><th>SSN/NIK</th><th>Aksi</th></tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>{{ $user->id ?? $user->id }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>
                <form action="/admin/users/{{ $user->id }}/role" method="POST" class="d-inline">
                    @csrf
                    @method('PUT')
                    <select name="role" onchange="this.form.submit()" class="form-select form-select-sm d-inline" style="width: auto;">
                        @foreach(['user', 'editor', 'admin'] as $role)
                            <option value="{{ $role }}" {{ ($user->role ?? '') == $role ? 'selected' : '' }}>{{ $role }}</option>
                        @endforeach
                    </select>
                </form>
            </td>
            {{-- ✅ FIX A02: Gunakan masked_ssn --}}
            <td>{{ $user->maskSsn($user->ssn) ?? 'N/A' }}</td>
            <td>
                <a href="/profile/{{ $user->id }}/export" class="btn btn-sm btn-info">Export</a>
                <form action="/admin/users/{{ $user->id }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-danger">Hapus</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="mt-4">
    <a href="/admin/system-info" class="btn btn-warning" target="_blank">🔧 System Info (JSON)</a>
</div>
@endsection
