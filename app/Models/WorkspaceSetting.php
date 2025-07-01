<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkspaceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Get the workspace that owns the setting.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
    public function value(): Attribute {
		return Attribute::make(
			set: fn($value) => json_encode(
				collect($value)
					->reject(fn($item) => is_null($item) || $item === "")
					->toArray()
			)
		);
	}
} 