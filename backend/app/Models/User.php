<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;


class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    public function hasNewBadge(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->badges) return false;

                $lastLogin = $this->last_login_at ?? now()->subYear();
                foreach ($this->badges as $key => $value) {
                    if (str_ends_with($key, '_date') && $value && Carbon::parse($value)->isAfter($lastLogin)) {
                        return true;
                    }
                }
                return false;
            }
        );
    }
    
    protected $appends = ['has_new_badge'];
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'heart_score',
        'streak',
        'completed_dates'
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'badges' => 'array',
        'last_dhikr_date' => 'date',
        'password' => 'hashed',
        'completed_dates' => 'array'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function dhikrs()
    {
        return $this->hasMany(Dhikr::class);
    }

}
