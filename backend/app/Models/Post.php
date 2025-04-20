<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'slug',
        'content',
        'image',
        'excerpt',
        'published_at',
        'user_id',
        'status', // draft, published
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Get the user that authored the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now());
    }

    /**
     * Get the categories for the post.
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
} 