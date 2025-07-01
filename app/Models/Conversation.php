<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ConversationRole;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Conversation extends Model
{
    protected $fillable = [
        'uuid',
        'workspace_id',
        'type',
        'type_source',
        'ip_address',
        'user_agent',
        'source',
        'query_string',
        'read_at',
    ];

    protected $casts = [
		'read_at' => 'datetime'
	];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
    public function message(): HasOne {
		return $this->messages()->one();
	}

    public function messages(): HasMany
    {
        return $this->hasMany(ConversationMessage::class);
    }


    public function userMessage(): HasOne
    {
		return $this->message()->where('role', ConversationRole::USER)->latest();
	}

	public function assistantMessage(): HasOne
    {
		return $this->message()->where('role', ConversationRole::ASSISTANT)->latest();
	}

	public function lead(): HasOne
    {
		return $this->hasOne(Lead::class);
	}

	public function refundRequests(): HasMany
    {
		return $this->hasMany(RefundRequest::class);
	}
}
