<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceLoad extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'loads',
        'created_at',
    ];

    // Disable default timestamps as we only have created_at in the database
    public $timestamps = false;

    /**
     * Get the workspace that owns the load.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
} 