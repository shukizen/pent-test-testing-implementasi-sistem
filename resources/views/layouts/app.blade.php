<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- VULNERABLE A05: Missing security headers (CSP, X-Frame-Options, etc.) --}}
    <title>{{ config('app.name') }} - @yield('title', 'Home')</title>
    <!-- ✅ FIX: Tambahkan integrity hash dan crossorigin attribute -->
<script
    src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha384-1H217gwSVyLSIfaLxHbE7dRb3v4mYCKbpQvzx0cegeju1MVsGrX5xXxAvs/HgeFs"
    crossorigin="anonymous"></script>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
    rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YcnS/1i4l8nfIUMJp2z9sF6oQkLm3/J6mR"
    crossorigin="anonymous">

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/">🔓 VulnApp Pentest</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="/posts">Posts</a></li>
                    @auth
                    <li class="nav-item"><a class="nav-link" href="/notes">Notes</a></li>
                    <li class="nav-item"><a class="nav-link" href="/dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/files/upload">Upload</a></li>
                    <li class="nav-item"><a class="nav-link" href="/admin/dashboard">Admin</a></li>
                    @endauth
                </ul>
                <ul class="navbar-nav">
                    @auth
                    <li class="nav-item"><a class="nav-link" href="/profile/{{ Auth::id() }}">{{ Auth::user()->name }}</a></li>
                    <li class="nav-item">
                        <form action="/logout" method="POST" class="d-inline">
                            @csrf
                            <button class="nav-link btn btn-link" type="submit">Logout</button>
                        </form>
                    </li>
                    @else
                    <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="/register">Register</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger">
            @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        @yield('content')
    </div>

    @yield('scripts')
</body>

</html>