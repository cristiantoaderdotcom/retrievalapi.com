<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeBaseResourceDuplicate extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'knowledge_base_id',
        'text',
    ];

    /**
     * Get the knowledge base that owns the duplicate.
     */
    public function knowledgeBase(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBase::class);
    }
} 