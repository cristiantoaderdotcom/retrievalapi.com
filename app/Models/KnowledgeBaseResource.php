<?php

namespace App\Models;

use App\Enums\ResourceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class KnowledgeBaseResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'resourceable_type',
        'resourceable_id',
        'workspace_id',
        'words_count',
        'characters_count',
        'status',
        'error_message',
        'process_started_at',
        'process_completed_at',
    ];

    protected $casts = [
        'status' => ResourceStatus::class,
        'process_started_at' => 'datetime',
        'process_completed_at' => 'datetime',
    ];

    /**
     * Get the parent resourceable model.
     */
    public function resourceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function contexts(): HasMany {
		return $this->hasMany(KnowledgeBase::class, 'knowledge_base_resource_id');
	}

    /**
     * Get the knowledge bases that reference this resource.
     */
    public function knowledgeBases(): HasMany
    {
        return $this->hasMany(KnowledgeBase::class, 'knowledge_base_resource_id');
    }
} 