<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Collection extends Model
{
    protected $fillable = [
        'name',
        'type'
    ];

    /**
     * The adhkars that belong to the collection.
     */
    public function adhkars(): BelongsToMany
    {
        return $this->belongsToMany(Adhkar::class, 'collection_adhkars');
    }
} 