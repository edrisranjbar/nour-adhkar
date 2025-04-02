<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Donation extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'amount',
        'transaction_id',
        'reference_id',
        'card_pan',
        'status',
        'description',
        'paid_at',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'integer',
        'paid_at' => 'datetime',
    ];
    
    /**
     * Get the user who made the donation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Scope a query to only include successful donations.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed');
    }
    
    /**
     * Scope a query to only include recent donations.
     */
    public function scopeRecent($query, $limit = 5)
    {
        return $query->where('status', 'completed')
                     ->orderBy('paid_at', 'desc')
                     ->limit($limit);
    }
}
