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

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'active',
        'avatar',
        'score',
        'streak',
        'badges',
        'heart_score',
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
        'score' => 'integer',
        'badges' => 'json',
        'favorites' => 'json',
        'completed_dates' => 'json',
        'heart_score' => 'integer',
        'last_login_at' => 'datetime',
        'last_dhikr_completed_at' => 'datetime',
        'total_dhikrs' => 'integer'
    ];

    protected $attributes = [
        'role' => 'user',
        'active' => true,
        'score' => 0,
        'streak' => 0,
        'badges' => '[]',
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

    public function getStreakAttribute()
    {
        $streak = 0;
        $dates = $this->completed_dates ?? [];
        sort($dates);
        
        if (!empty($dates)) {
            $lastDate = end($dates);
            $currentStreak = 1;
            
            // If last completion was today, count the streak
            $lastCompletion = \Carbon\Carbon::parse($lastDate);
            $now = now();
            
            if ($lastCompletion->isToday()) {
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

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    public function league()
    {
        return $this->belongsTo(League::class);
    }
}
