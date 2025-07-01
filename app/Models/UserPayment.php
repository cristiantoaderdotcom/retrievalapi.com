<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPayment extends Model {

    protected $fillable = [
        'user_id',
        'uuid',
        'referral_id',
        'amount_total',
        'status',
    ];

    protected $casts = [
        'amount_total' => 'decimal:2',
    ];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function referral(): BelongsTo {
        return $this->belongsTo(Referral::class);
    }
}
