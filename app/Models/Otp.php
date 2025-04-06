<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Otp extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','otp_code','type', 'is_used', 'expires_at'];
    protected $casts = [
        'is_used' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
