<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeBase extends Model
{
    use HasFactory;
    
    protected $table = 'knowledge_bases';

    protected $fillable = [
        'workspace_id',
        'product_id',
        'knowledge_base_resource_id',
        'question',
        'answer',
        'embedding',
        'embedding_processed_at',
        'similarity_score',
    ];

    protected $casts = [
        'embedding_processed_at' => 'datetime',
        'similarity_score' => 'float',
    ];

    /**
     * Get the workspace that owns the knowledge base.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the resource that owns the knowledge base.
     */
    public function knowledgeBaseResource(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBaseResource::class);
    }

    /**
     * Get the duplicates for the knowledge base.
     */
    public function duplicates(): HasMany
    {
        return $this->hasMany(KnowledgeBaseResourceDuplicate::class);
    }
    
    /**
     * Get the combined text for embedding processing.
     * This combines both question and answer to create a more comprehensive embedding.
     */
    public function getTextForEmbedding(): string
    {
        return trim($this->question . ' ' . $this->answer);
    }
} 