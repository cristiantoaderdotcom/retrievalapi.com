<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcessedEmail extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'email_inbox_id',
        'message_id',
        'subject',
        'from_email',
        'from_name',
        'original_message',
        'ai_response',
        'total_tokens',
        'was_replied',
        'replied_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'total_tokens' => 'integer',
        'was_replied' => 'boolean',
        'replied_at' => 'datetime',
    ];

    /**
     * Get the inbox that this email belongs to.
     */
    public function inbox(): BelongsTo
    {
        return $this->belongsTo(EmailInbox::class, 'email_inbox_id');
    }

    /**
     * Scope a query to only include emails that haven't been processed yet.
     */
    public function scopeNotReplied($query)
    {
        return $query->where('was_replied', false);
    }

    /**
     * Mark the email as replied.
     */
    public function markAsReplied(string $aiResponse, int $totalTokens): void
    {
        $this->update([
            'ai_response' => $aiResponse,
            'total_tokens' => $totalTokens,
            'was_replied' => true,
            'replied_at' => now(),
        ]);
    }
} 