<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class KnowledgeBaseTextResource extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $fillable = [
        'content',
    ];

    /**
     * Get the resource record associated with the text resource.
     */
    public function resource(): MorphOne
    {
        return $this->morphOne(KnowledgeBaseResource::class, 'resourceable');
    }
} 