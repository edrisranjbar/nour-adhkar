<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Collection extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'icon',
        'slug'
    ];

    /**
     * Get the adhkar for the collection.
     */
    public function adhkar(): HasMany
    {
        return $this->hasMany(Adhkar::class);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($collection) {
            if (empty($collection->slug)) {
                $collection->slug = $collection->generateUniqueSlug($collection->name);
            }
        });

        static::updating(function ($collection) {
            if ($collection->isDirty('name') && !$collection->isDirty('slug')) {
                $collection->slug = $collection->generateUniqueSlug($collection->name);
            }
        });
    }

    /**
     * Generate a unique slug.
     */
    protected function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $count = static::whereRaw("slug RLIKE '^{$slug}(-[0-9]+)?$'")->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }
} 