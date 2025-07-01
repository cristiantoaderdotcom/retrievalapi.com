<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class KnowledgeBaseFileResource extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $fillable = [
        'name',
        'path',
        'type',
        'size',
    ];

    /**
     * Get the resource record associated with the file resource.
     */
    public function resource(): MorphOne
    {
        return $this->morphOne(KnowledgeBaseResource::class, 'resourceable');
    }
} 