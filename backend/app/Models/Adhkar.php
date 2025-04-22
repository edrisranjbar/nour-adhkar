<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Adhkar extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'arabic_text',
        'translation',
        'count',
        'prefix',
        'collection_id'
    ];

    protected $casts = [
        'count' => 'integer'
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
} 