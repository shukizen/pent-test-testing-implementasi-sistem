@extends('layouts.app')
@section('title', 'Verifikasi 2FA')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Dua-Faktor Autentikasi (2FA)</div>
            <div class="card-body">
                <p class="text-center">Masukkan 6 digit kode OTP dari aplikasi Authenticator Anda untuk melanjutkan.</p>
                
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form method="POST" action="/2fa-verify">
                    @csrf
                    <div class="mb-3 text-center">
                        <label class="form-label">Kode Otentikasi (OTP)</label>
                        <input type="text" name="one_time_password" class="form-control text-center fs-4" style="letter-spacing: 5px;" maxlength="6" placeholder="000000" autofocus required autocomplete="off">
                        @error('one_time_password')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="d-grid mt-3">
                        <button type="submit" class="btn btn-primary">Verifikasi & Masuk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
