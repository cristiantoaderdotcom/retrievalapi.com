<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ConversationRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ConversationMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'role',
        'message',
        'score',
        'total_tokens',
        'metadata',
        'disliked',
    ];

    protected $casts = [
		'role' => ConversationRole::class,
		'revised_at' => 'datetime',
		'metadata' => 'array',
		'disliked' => 'boolean',
	];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
