<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get the blog posts that belong to this category.
     */
    public function posts()
    {
        return $this->belongsToMany(BlogPost::class, 'blog_post_category', 'category_id', 'blog_post_id');
    }

    /**
     * Get all posts including those from child categories.
     */
    public function allPosts()
    {
        $postIds = $this->posts()->pluck('blog_posts.id');
        
        foreach ($this->children as $child) {
            $postIds = $postIds->merge($child->allPosts()->pluck('blog_posts.id'));
        }
        
        return BlogPost::whereIn('id', $postIds);
    }
} 