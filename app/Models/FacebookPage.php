<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacebookPage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'workspace_id',
        'uuid',
        'page_id',
        'page_name',
        'page_access_token',
        'page_verify_token',
        'page_icon',
        'handle_messages',
        'handle_comments',
        'last_message_at',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'handle_messages' => 'boolean',
        'handle_comments' => 'boolean',
        'last_message_at' => 'datetime',
    ];

    /**
     * Get the user that owns the Facebook page.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the workspace that owns the Facebook page.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
} 