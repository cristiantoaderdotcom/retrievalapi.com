<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Referral extends Model {
    protected $fillable = [
        'user_id',
        'code',
        'description',
        'status',
        'clicks',
        'max_uses',
        'comission_rate',
        'expires_at',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getReferralLinkAttribute() {
        return route('offers.show', $this->code);
    }

    public function referrers() {
        return $this->hasMany(UserReferral::class, 'referral_id', 'id');
    }

    public function payments(): HasManyThrough {
        return $this->hasManyThrough(UserPayment::class, UserReferral::class, 'referral_id', 'user_id', 'id', 'user_id');
    }

    public function paidPayments(): HasManyThrough {
        return $this->payments()
        ->where('status', 'paid');
    }

    public function pendingPayments(): HasManyThrough {
        return $this->payments()
        ->where('status', 'pending')
        ->whereDate('created_at', '<=', now()->subDays(30));
    }

    public function availablePayments(): HasManyThrough {
        return $this->payments()
        ->where('status', 'pending')
        ->whereDate('created_at', '>=', now()->subDays(30));
    }

    public function getConversionRateAttribute(): float {
        if ($this->clicks === 0) {
            return 0.0;
        }
        return round(($this->referrers_count / $this->clicks) * 100, 2);
    }

    public function getTotalCommissionAttribute() {
        return $this->payments_sum_amount_total * $this->commission_rate / 100;
    }

    public function getTotalPendingCommissionAttribute() {
        return $this->pending_payments_sum_amount_total * $this->commission_rate / 100;
    }

    public function getTotalPaidCommissionAttribute() {
        return $this->paid_payments_sum_amount_total * $this->commission_rate / 100;
    }
}
