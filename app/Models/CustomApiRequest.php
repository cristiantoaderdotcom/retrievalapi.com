<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomApiRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'conversation_id',
        'custom_api_integration_id',
        'request_data',
        'response_data',
        'status',
        'error_message',
        'response_time',
        'http_status_code',
        'raw_response',
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function customApiIntegration(): BelongsTo
    {
        return $this->belongsTo(CustomApiIntegration::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SUCCESS => 'Success',
            self::STATUS_FAILED => 'Failed',
            default => 'Unknown',
        };
    }

    public function getFormattedResponseTimeAttribute(): string
    {
        if (!$this->response_time) {
            return 'N/A';
        }

        if ($this->response_time < 1000) {
            return $this->response_time . 'ms';
        }

        return round($this->response_time / 1000, 2) . 's';
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SUCCESS => 'Success',
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

    public function scopeByIntegration($query, $integrationId)
    {
        return $query->where('custom_api_integration_id', $integrationId);
    }
}
