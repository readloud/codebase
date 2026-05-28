// app/Models/User.php
<?php

namespace App\Models;

// Gunakan ini jika Sanctum sudah terinstall
use Laravel\Sanctum\HasApiTokens;

// Atau comment dulu sementara jika belum install
// use Laravel\Sanctum\HasApiTokens;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    // Comment dulu jika Sanctum belum terinstall
    // use HasApiTokens, Notifiable;
    
    use Notifiable; // Temporary - only use Notifiable first

    protected $fillable = [
        'username', 'email', 'phone', 'password', 'role_id',
        'balance', 'verified_phone', 'provider', 'provider_id'
    ];

    protected $hidden = ['password', 'phone_otp'];

    protected $casts = [
        'balance' => 'decimal:2',
        'verified_phone' => 'boolean',
        'phone_otp_expires_at' => 'datetime'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function hasPermission($permission)
    {
        return $this->role->permissions->contains('name', $permission);
    }
}
