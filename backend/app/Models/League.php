<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class League extends Model
{
    protected $fillable = [
        'name',
        'description',
        'min_points',
        'max_points',
        'icon',
        'color'
    ];

    protected $casts = [
        'min_points' => 'integer',
        'max_points' => 'integer'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public static function getLeagueForPoints(int $points): ?League
    {
        return self::where('min_points', '<=', $points)
            ->where('max_points', '>=', $points)
            ->first();
    }

    public static function getNextLeague(League $currentLeague): ?League
    {
        return self::where('min_points', '>', $currentLeague->min_points)
            ->orderBy('min_points')
            ->first();
    }

    public static function getPreviousLeague(League $currentLeague): ?League
    {
        return self::where('min_points', '<', $currentLeague->min_points)
            ->orderBy('min_points', 'desc')
            ->first();
    }
}