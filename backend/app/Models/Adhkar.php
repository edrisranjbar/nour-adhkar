<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adhkar extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'count',
        'category',
        'description',
        'completed_at',
        'collection_id'
    ];

    protected $casts = [
        'completed_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }
} 