<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Collection extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'icon'
    ];

    /**
     * Get the adhkar for the collection.
     */
    public function adhkar(): HasMany
    {
        return $this->hasMany(Adhkar::class);
    }
} 