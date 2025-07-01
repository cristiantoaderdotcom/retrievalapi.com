<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeBaseVideoResource extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'workspace_id',
        'url',
        'title',
        'content',
    ];

    /**
     * Get the resource record associated with the text resource.
     */
    public function resource(): MorphOne {
        return $this->morphOne(KnowledgeBaseResource::class, 'resourceable');
    }

    public function workspace(): BelongsTo {
        return $this->belongsTo(Workspace::class);
    }
}