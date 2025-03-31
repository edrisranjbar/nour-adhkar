<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dhikr extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'count',
        'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 