@extends('layouts.app')
@section('title', 'Setup 2FA')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Setup Two-Factor Authentication (MFA)</div>
            <div class="card-body text-center">
                <p>Pindai QR code ini dengan aplikasi Authenticator Anda (Google Authenticator/Authy) atau masukkan kode rahasia secara manual.</p>
                
                <div class="mb-4 mt-4">
                    {{-- Tampilkan SVG QR Code jika memungkinkan, atau link/teks manual --}}
                    <div style="background: white; padding: 10px; display: inline-block; border: 1px solid #ddd;">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}" alt="QR Code for MFA">
                    </div>
                </div>

                <div class="mb-3 mt-3">
                    <strong>Secret Key:</strong> <br>
                    <code>{{ $secret }}</code>
                </div>

                <a href="/dashboard" class="btn btn-success">Selesai & Lanjut ke Dashboard</a>
            </div>
        </div>
    </div>
</div>
@endsection
