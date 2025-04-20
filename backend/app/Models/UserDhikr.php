<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDhikr extends Model
{
    use HasFactory;

    protected $table = 'user_dhikrs';

    protected $fillable = [
        'title',
        'arabic_text',
        'translation',
        'count',
        'user_id',
        'is_completed',
        'completed_count'
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_count' => 'integer',
        'count' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 