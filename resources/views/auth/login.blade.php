@extends('layouts.app')
@section('title', 'Login')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Login</div>
            <div class="card-body">
                {{-- VULNERABLE A07: No CAPTCHA, no rate limiting --}}
                <form method="POST" action="/login">
                    @csrf
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                        {{-- VULNERABLE A07: Error messages reveal if email exists --}}
                    </div>
                    <div class="mb-3">
                        {!! NoCaptcha::display() !!}
                        @if ($errors->has('g-recaptcha-response'))
                            <span class="text-danger"><small>{{ $errors->first('g-recaptcha-response') }}</small></span>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                    <a href="/register" class="btn btn-link">Belum punya akun?</a>
                    {!! NoCaptcha::renderJs() !!}
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
