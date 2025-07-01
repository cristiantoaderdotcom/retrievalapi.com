<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeBaseUrlResource extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $fillable = [
        'url',
        'is_primary',
        'priority_score',
        'workspace_id',
    ];

     protected $casts = [
        'priority_score' => 'integer',
        'is_primary' => 'boolean',
    ];

    public function resource(): MorphOne
    {
        return $this->morphOne(KnowledgeBaseResource::class, 'resourceable');
    }
    
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
} 