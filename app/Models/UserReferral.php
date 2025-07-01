<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReferral extends Model {
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'referral_id',
        'uuid',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function referral(): BelongsTo {
        return $this->belongsTo(Referral::class);
    }
}
