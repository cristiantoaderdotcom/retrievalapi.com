<?php

namespace App\Models;

use App\Enums\ProductFeedStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductFeed extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'workspace_id',
        'name',
        'url',
        'provider',
        'scan_frequency',
        'last_processed_at',
        'status',
        'error_message',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_processed_at' => 'datetime',
        'scan_frequency' => 'integer',
        'status' => ProductFeedStatus::class,
    ];

    /**
     * Status constants
     */
    const STATUS_IDLE = 0;
    const STATUS_PROCESSING = 1;
    const STATUS_ERROR = 2;

    /**
     * Get the workspace that owns this feed.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the products for this feed.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Check if feed is due for processing.
     */
    public function isDueForProcessing(): bool
    {
        if (!$this->last_processed_at) {
            return true;
        }

        return $this->last_processed_at->addMinutes($this->scan_frequency)->isPast();
    }
} 