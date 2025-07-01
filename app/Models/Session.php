<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model {
	protected $table = 'sessions';
	public $timestamps = false;

	protected $casts = [
		'id' => 'string'
	];

	public function getUnserializedPayloadAttribute(): array {
		if(empty($this->payload))
			return [];

		return unserialize(base64_decode($this->payload));
	}

	public function user(): BelongsTo {
		return $this->belongsTo(User::class);
	}

	public function getLastActivityAtAttribute(): Carbon {
		return Carbon::createFromTimestamp($this->last_activity);
	}
}
