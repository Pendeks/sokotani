<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'buyer_id', 'quantity', 'current_unit_price', 'agreed_unit_price',
        'status', 'fulfillment_method', 'fulfillment_notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'current_unit_price' => 'integer',
        'agreed_unit_price' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function negotiations(): HasMany
    {
        return $this->hasMany(OfferNegotiation::class);
    }
}
