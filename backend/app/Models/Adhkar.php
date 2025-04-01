<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Adhkar extends Model
{
    protected $fillable = [
        'title',
        'count',
        'prefix',
        'arabic_text',
        'translation',
        'collection_id'
    ];

    /**
     * The collections that belong to the adhkar.
     */
    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'collection_adhkars');
    }
} 