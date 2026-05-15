<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    // ✅ FIX: Jangan biarkan 'role' bisa diisi via mass assignment
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'bio'
    ];

    // ✅ FIX: Accessor dan Mutator untuk enkripsi SSN dengan fallback data plaintext lama
    protected function ssn(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) return null;
                try {
                    return Crypt::decryptString($value);
                } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                    return $value;
                }
            },
            set: fn($value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    protected function phone(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!$value) return null;
                try {
                    return Crypt::decryptString($value);
                } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                    return $value;
                }
            },
            set: fn($value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    public function maskSsn($ssn)
    {
        if (!$ssn) return null;
        return substr($ssn, 0, 4) . '********' . substr($ssn, -4);
    }

    public function maskPhone($phone)
    {
        if (!$phone) return null;
        return substr($phone, 0, 4) . '****' . substr($phone, -3);
    }

    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
