<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Lead extends Model
{
    protected $fillable = [
        'workspace_id',
        'conversation_id',
        'name',
        'email',
        'phone',
        'source_type',
        'ip_address',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
