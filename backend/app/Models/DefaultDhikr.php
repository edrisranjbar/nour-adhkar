<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DefaultDhikr extends Model
{
    protected $fillable = [
        'title',
        'count',
        'prefix',
        'suffix',
        'translation'
    ];
} 