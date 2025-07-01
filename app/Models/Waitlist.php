<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waitlist extends Model
{
    use HasFactory;

    protected $table = 'waitlist';

    protected $fillable = [
        'name',
        'email',
        'business_name',
        'website',
        'niche',
        'platforms',
        'desired_features',
        'submitted_at',
    ];

    protected $casts = [
        'platforms' => 'array',
        'submitted_at' => 'datetime',
    ];
}
