<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;


class User extends Authenticatable implements JWTSubject, CanResetPasswordContract
{
    use Notifiable;
    use HasFactory;
    use CanResetPasswordTrait;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'active',
        'avatar',
        'streak',
        'last_login_at',
        'last_dhikr_completed_at',
        'favorites',
        'total_dhikrs'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'id' => 'integer',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean',
        'favorites' => 'json',
        'completed_dates' => 'json',
        'daily_counts' => 'json',
        'last_login_at' => 'datetime',
        'last_dhikr_completed_at' => 'datetime',
        'total_dhikrs' => 'integer'
    ];

    protected $attributes = [
        'role' => 'user',
        'active' => true,
        'streak' => 0,
        'favorites' => '[]',
        'total_dhikrs' => 0
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function userDhikrs()
    {
        return $this->hasMany(UserDhikr::class);
    }

    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeFavorite($query, $postId)
    {
        return $query->whereJsonContains('favorites', $postId);
    }
    
    protected $appends = [
        'streak'
    ];

    public function getStreakAttribute()
    {
        $streak = 0;
        $dates = $this->completed_dates ?? [];
        sort($dates);
        
        if (!empty($dates)) {
            $lastDate = end($dates);
            $currentStreak = 1;
            
            $lastCompletion = \Carbon\Carbon::parse($lastDate);
            
            if ($lastCompletion->isToday()) {
                for ($i = count($dates) - 2; $i >= 0; $i--) {
                    $currentDate = \Carbon\Carbon::parse($dates[$i]);
                    $expectedDate = \Carbon\Carbon::parse($dates[$i + 1])->subDay();
                    
                    if ($currentDate->format('Y-m-d') === $expectedDate->format('Y-m-d')) {
                        $currentStreak++;
                    } else {
                        break;
                    }
                }
                $streak = $currentStreak;
            }
        }
        
        return $streak;
    }
}
