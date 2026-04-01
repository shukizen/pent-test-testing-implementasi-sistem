@extends('layouts.app')
@section('title', 'Home')
@section('content')
<div class="text-center py-5">
    <h1>🔓 VulnApp Pentest Lab</h1>
    <p class="lead">Platform latihan keamanan web berbasis OWASP Top 10 (2021)</p>
    <hr>
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card border-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title text-danger">⚠️ Peringatan</h5>
                    <p>Aplikasi ini <strong>sengaja dibuat vulnerable</strong> untuk tujuan edukasi. JANGAN deploy ke production!</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title text-primary">�� OWASP Top 10</h5>
                    <p>Pelajari dan praktikkan kerentanan A01 sampai A10 dengan panduan lengkap di folder <code>pentest/</code></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success mb-3">
                <div class="card-body">
                    <h5 class="card-title text-success">🔑 Akun Demo</h5>
                    <p><code>admin@pentest.local</code> / <code>admin123</code><br>
                    <code>budi@pentest.local</code> / <code>password</code><br>
                    <code>siti@pentest.local</code> / <code>123456</code></p>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <a href="/login" class="btn btn-primary btn-lg me-2">Login</a>
        <a href="/register" class="btn btn-outline-primary btn-lg">Register</a>
    </div>
</div>
@endsection
