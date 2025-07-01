<?php

namespace App\Models;

use App\Traits\HasSubscriptionLimits;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail {
	use HasFactory, Notifiable, HasApiTokens, HasRoles;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'uuid',
		'name',
		'email',
		'avatar',
		'password',
		'email_verified_at',
		'status',
		'standard',
		'pro',
		'messages_limit',
		'context_limit',
		'last_pro_reminder_at',
	];

	/**
	 * The attributes that should be hidden for serialization.
	 *
	 * @var array<int, string>
	 */
	protected $hidden = [
		'password',
		'remember_token',
	];

	/**
	 * Get the attributes that should be cast.
	 *
	 * @return array<string, string>
	 */
	protected function casts(): array {
		return [
			'email_verified_at' => 'datetime',
			'password' => 'hashed',
			'standard' => 'boolean',
			'pro' => 'boolean',
			'status' => 'boolean',
			'last_pro_reminder_at' => 'datetime',
		];
	}

	public function workspaces(): HasMany {
		return $this->hasMany(Workspace::class);
	}

	public function payments(): HasMany {
		return $this->hasMany(UserPayment::class);
	}

	public function getPlanAttribute(): string {
		if ($this->pro) {
			return 'Pro';
		}

		return 'Standard';
	}
}
