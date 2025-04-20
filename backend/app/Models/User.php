<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use HasFactory;
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
    
    protected $appends = [
        'has_new_badge',
        'streak'
    ];
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'heart_score',
        'streak',
        'completed_dates',
        'role',
        'active'
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
        'completed_dates' => 'array',
        'favorites' => 'array'
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

    public function getStreakAttribute()
    {
        $streak = 0;
        $dates = $this->completed_dates ?? [];
        sort($dates);
        
        if (!empty($dates)) {
            $lastDate = end($dates);
            $currentStreak = 1;
            
            // If last completion was yesterday or today, count the streak
            $lastCompletion = \Carbon\Carbon::parse($lastDate);
            $now = now();
            
            if ($lastCompletion->diffInDays($now) <= 1) {
                // Count backwards from the last date
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
