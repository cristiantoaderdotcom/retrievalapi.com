<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApiToken extends Model
{
    protected $fillable = [
        'workspace_id',
        'token',
        'name',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the workspace that owns the API token.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Generate a new token.
     */
    public static function generateToken(): string
    {
        return Str::random(64);
    }

    /**
     * Create a new token for a workspace.
     */
    public static function createForWorkspace(Workspace $workspace, string $name): self
    {
        return static::create([
            'workspace_id' => $workspace->id,
            'token' => static::generateToken(),
            'name' => $name,
        ]);
    }
}
