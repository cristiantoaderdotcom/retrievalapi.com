<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundRequest extends Model
{
    protected $fillable = [
        'conversation_id',
        'request_data',
        'status',
        'notes',
        'processed_at',
    ];

    protected $casts = [
        'request_data' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the conversation that owns this refund request.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_APPROVED = 'approved';
    const STATUS_DENIED = 'denied';
    const STATUS_COMPLETED = 'completed';

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_DENIED => 'Denied',
            self::STATUS_COMPLETED => 'Completed',
        ];
    }

    /**
     * Get a specific field from request_data
     */
    public function getRequestField(string $field): mixed
    {
        return data_get($this->request_data, $field);
    }

    /**
     * Set a specific field in request_data
     */
    public function setRequestField(string $field, mixed $value): void
    {
        $data = $this->request_data ?? [];
        data_set($data, $field, $value);
        $this->request_data = $data;
    }

    /**
     * Get the customer email from request data
     */
    public function getEmailAttribute(): ?string
    {
        return $this->getRequestField('email');
    }

    /**
     * Get the sale ID from request data
     */
    public function getSaleIdAttribute(): ?string
    {
        return $this->getRequestField('sale_id');
    }

    /**
     * Get the refund reason from request data
     */
    public function getReasonAttribute(): ?string
    {
        return $this->getRequestField('reason');
    }

    /**
     * Get all request fields as a formatted array
     */
    public function getFormattedRequestData(): array
    {
        $data = $this->request_data ?? [];
        $formatted = [];

        foreach ($data as $key => $value) {
            $label = ucwords(str_replace('_', ' ', $key));
            $formatted[$label] = $value;
        }

        return $formatted;
    }

    /**
     * Check if the request has a specific field
     */
    public function hasRequestField(string $field): bool
    {
        return array_key_exists($field, $this->request_data ?? []);
    }

    /**
     * Get the status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? ucfirst($this->status);
    }
}
