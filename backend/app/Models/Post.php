/**
 * Get the categories for the post.
 */
public function categories()
{
    return $this->belongsToMany(Category::class);
} 