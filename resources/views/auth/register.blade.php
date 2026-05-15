@extends('layouts.app')
@section('title', 'Register')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Register</div>
            <div class="card-body">
                <form method="POST" action="/register">
                    @csrf
                    <div class="mb-3">
                        <label>Nama</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        {{-- VULNERABLE A07: No password strength indicator --}}
                        <input type="password" name="password" class="form-control" required>
                        <small class="text-muted">Minimal 8 karakter</small>
                    </div>
                    {{-- VULNERABLE A01: Hidden field that can be manipulated --}}
                    <input type="hidden" name="role" value="user">
                    <div class="mb-3">
                        {!! NoCaptcha::display() !!}
                        @if ($errors->has('g-recaptcha-response'))
                        <span class="text-danger"><small>{{ $errors->first('g-recaptcha-response') }}</small></span>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary">Register</button>
                    {!! NoCaptcha::renderJs() !!}
                </form>
            </div>
        </div>
    </div>
</div>
@endsection