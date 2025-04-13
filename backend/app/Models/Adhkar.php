<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Adhkar extends Model
{
    protected $fillable = [
        'title',
        'count',
        'prefix',
        'arabic_text',
        'translation',
        'user_id',
        'is_custom',
    ];

    protected $casts = [
        'is_custom' => 'boolean',
        'count' => 'integer'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }
} 