<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferNegotiation extends Model
{
    protected $fillable = ['user_id', 'action', 'unit_price', 'message'];

    protected $casts = ['unit_price' => 'integer'];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
