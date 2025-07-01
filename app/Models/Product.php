<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'workspace_id',
        'product_feed_id',
        'external_id',
        'title',
        'handle',
        'body_html',
        'published_at',
        'external_created_at',
        'external_updated_at',
        'vendor',
        'product_type',
        'tags',
        'embedding',
        'embedding_processed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
        'external_created_at' => 'datetime',
        'external_updated_at' => 'datetime',
        'embedding_processed_at' => 'datetime',
        'tags' => 'array',
    ];

    /**
     * Get the workspace that owns this product.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the feed that owns this product.
     */
    public function feed(): BelongsTo
    {
        return $this->belongsTo(ProductFeed::class, 'product_feed_id');
    }

    /**
     * Get the variants for this product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the images for this product.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Get the options for this product.
     */
    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }

    /**
     * Get primary image URL (first image)
     */
    public function getPrimaryImageUrlAttribute(): ?string
    {
        return $this->images()->orderBy('position')->first()?->src;
    }
} 