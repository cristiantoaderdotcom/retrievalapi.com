<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'conversation_id',
        'booking_integration_id',
        'status',
        'request_data',
        'external_booking_id',
        'booking_url',
        'notes',
        'booking_date',
        'completed_at',
    ];

    protected $casts = [
        'request_data' => 'array',
        'booking_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function bookingIntegration(): BelongsTo
    {
        return $this->belongsTo(BookingIntegration::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_FAILED => 'Failed',
            default => 'Unknown',
        };
    }

    public function getRequestDataValueAttribute($key, $default = null)
    {
        return data_get($this->request_data, $key, $default);
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_FAILED => 'Failed',
        ];
    }

    public function scopeForWorkspace($query, $workspaceId)
    {
        return $query->whereHas('conversation', function ($q) use ($workspaceId) {
            $q->where('workspace_id', $workspaceId);
        });
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
} 