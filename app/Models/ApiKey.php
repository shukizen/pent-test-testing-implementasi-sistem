<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    // VULNERABLE A02: API secrets stored as plaintext
    protected $fillable = ['user_id', 'key', 'secret'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
