<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'category', 'description', 'unit', 'available_quantity',
        'minimum_quantity', 'asking_price_per_unit', 'location', 'image_path',
        'harvest_date', 'is_active',
    ];

    protected $casts = [
        'available_quantity' => 'decimal:2',
        'minimum_quantity' => 'decimal:2',
        'asking_price_per_unit' => 'integer',
        'harvest_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }
}
