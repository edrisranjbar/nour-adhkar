<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $fillable = [
        'title',
        'description',
        'category'
    ];

    public function adhkar()
    {
        return $this->hasMany(Adhkar::class);
    }
} 