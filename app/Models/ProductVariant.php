<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'external_id',
        'title',
        'option1',
        'option2',
        'option3',
        'sku',
        'requires_shipping',
        'taxable',
        'available',
        'price',
        'grams',
        'compare_at_price',
        'position',
        'external_created_at',
        'external_updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requires_shipping' => 'boolean',
        'taxable' => 'boolean',
        'available' => 'boolean',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'grams' => 'integer',
        'position' => 'integer',
        'external_created_at' => 'datetime',
        'external_updated_at' => 'datetime',
    ];

    /**
     * Get the product that owns this variant.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the discount percentage if compare_at_price is set
     */
    public function getDiscountPercentageAttribute(): ?int
    {
        if (!$this->compare_at_price || !$this->price || $this->compare_at_price <= $this->price) {
            return null;
        }

        return (int)(($this->compare_at_price - $this->price) / $this->compare_at_price * 100);
    }
} 