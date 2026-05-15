<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\RateLimiter;

// Public routes
Route::get('/', function () {
    return view('welcome');
});

// Auth routes - SECURED with rate limiting (A04)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login'); // ✅ Menggunakan custom rate limiter 'login' dengan pesan kesalahan khusus
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password reset - SECURED with rate limiting (A04)
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:forgot-password');
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Posts - some routes lack auth middleware
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/search', [PostController::class, 'search']); // VULNERABLE A03: SQL injection
Route::get('/posts/{id}', [PostController::class, 'show']);

// VULNERABLE A01: These routes use auth but no ownership check inside controller
Route::middleware(['auth', '2fa'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Posts (authenticated)
    Route::get('/posts/create/new', [PostController::class, 'create']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{id}/edit', [PostController::class, 'edit']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);

    // Notes - VULNERABLE A01: IDOR
    Route::get('/notes', [NoteController::class, 'index']);
    Route::get('/notes/{id}', [NoteController::class, 'show']);
    Route::post('/notes', [NoteController::class, 'store']);
    Route::delete('/notes/{id}', [NoteController::class, 'destroy']);

    // Profile - VULNERABLE A01/A02
    Route::get('/profile/{id}', [ProfileController::class, 'show']);
    Route::get('/profile/edit/me', [ProfileController::class, 'edit']);
    Route::put('/profile/update', [ProfileController::class, 'update']);
    Route::post('/profile/api-key', [ProfileController::class, 'generateApiKey']);
    Route::get('/profile/{userId}/api-keys', [ProfileController::class, 'apiKeys']);
    Route::get('/profile/{id}/export', [ProfileController::class, 'exportData']);

    // File operations - VULNERABLE A08/A10
    Route::get('/files/upload', [FileController::class, 'showUpload']);
    Route::post('/files/upload', [FileController::class, 'upload']);
    Route::post('/files/fetch-url', [FileController::class, 'fetchUrl']);
    Route::get('/files/proxy-image', [FileController::class, 'proxyImage']);
    Route::post('/files/convert', [FileController::class, 'convertFile']);
    Route::post('/files/import', [FileController::class, 'importData']);

    // 2FA (MFA) - SECURED (A07)
    Route::get('/2fa-setup', [AuthController::class, 'enable2FA'])->name('2fa.setup');
    Route::get('/2fa-verify', [AuthController::class, 'show2FAVerify'])->name('2fa.verify');
    Route::post('/2fa-verify', [AuthController::class, 'verify2FA']);

    // Admin - SECURED with 'admin' middleware
    Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/users/search', [AdminController::class, 'searchUsers']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        Route::put('/users/{id}/role', [AdminController::class, 'updateUserRole']);
        Route::get('/system-info', [AdminController::class, 'systemInfo']);
    });
});

// API routes - VULNERABLE A01: No authentication on some endpoints
Route::prefix('api/v1')->middleware('auth')->group(function () {
    Route::get('/users', [ApiController::class, 'listUsers'])->middleware('admin'); // Hanya admin
    // ...

    Route::get('/posts/search', [ApiController::class, 'searchPosts']); // VULNERABLE A03: SQLi
    Route::post('/auth/verify', [ApiController::class, 'authenticatedEndpoint']);
    Route::post('/posts/bulk-delete', [ApiController::class, 'bulkDeletePosts']); // VULNERABLE A01/A09
    Route::post('/webhook', [ApiController::class, 'processWebhook']); // VULNERABLE A08
});
