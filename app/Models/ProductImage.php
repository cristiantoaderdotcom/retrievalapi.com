<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'external_id',
        'position',
        'src',
        'width',
        'height',
        'variant_ids',
        'external_created_at',
        'external_updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'position' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'variant_ids' => 'array',
        'external_created_at' => 'datetime',
        'external_updated_at' => 'datetime',
    ];

    /**
     * Get the product that owns this image.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
} 