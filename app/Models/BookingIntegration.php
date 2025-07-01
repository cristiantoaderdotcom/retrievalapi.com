<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingIntegration extends Model
{
    use HasFactory;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ERROR = 'error';

    const PLATFORM_CALENDLY = 'calendly';
    const PLATFORM_CAL_COM = 'cal_com';
    const PLATFORM_GOOGLE_CALENDAR = 'google_calendar';
    const PLATFORM_CUSTOM = 'custom';

    protected $fillable = [
        'workspace_id',
        'platform',
        'name',
        'status',
        'configuration',
        'trigger_keywords',
        'confirmation_message',
        'ai_instructions',
        'is_default',
    ];

    protected $casts = [
        'configuration' => 'array',
        'trigger_keywords' => 'array',
        'is_default' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function bookingRequests(): HasMany
    {
        return $this->hasMany(BookingRequest::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_ERROR => 'Error',
            default => 'Unknown',
        };
    }

    public function getPlatformLabelAttribute(): string
    {
        return match ($this->platform) {
            self::PLATFORM_CALENDLY => 'Calendly',
            self::PLATFORM_CAL_COM => 'Cal.com',
            self::PLATFORM_GOOGLE_CALENDAR => 'Google Calendar',
            self::PLATFORM_CUSTOM => 'Custom Platform',
            default => ucwords(str_replace('_', ' ', $this->platform)),
        };
    }

    public function getTriggerKeywordsStringAttribute(): string
    {
        return is_array($this->trigger_keywords) ? implode(', ', $this->trigger_keywords) : '';
    }

    public function getConfigurationValueAttribute($key, $default = null)
    {
        return data_get($this->configuration, $key, $default);
    }

    public static function getAvailablePlatforms(): array
    {
        return [
            self::PLATFORM_CALENDLY => [
                'label' => 'Calendly',
                'description' => 'Integrate with your Calendly scheduling page',
                'icon' => 'calendar',
                'fields' => [
                    'booking_url' => [
                        'label' => 'Calendly Booking URL',
                        'type' => 'url',
                        'required' => true,
                        'placeholder' => 'https://calendly.com/your-username/meeting',
                        'description' => 'Your Calendly booking page URL'
                    ]
                ]
            ],
            self::PLATFORM_CAL_COM => [
                'label' => 'Cal.com',
                'description' => 'Integrate with your Cal.com scheduling page',
                'icon' => 'calendar-days',
                'fields' => [
                    'booking_url' => [
                        'label' => 'Cal.com Booking URL',
                        'type' => 'url',
                        'required' => true,
                        'placeholder' => 'https://cal.com/your-username/meeting',
                        'description' => 'Your Cal.com booking page URL'
                    ]
                ]
            ],
            self::PLATFORM_GOOGLE_CALENDAR => [
                'label' => 'Google Calendar',
                'description' => 'Integrate with Google Calendar appointment slots',
                'icon' => 'calendar',
                'fields' => [
                    'booking_url' => [
                        'label' => 'Google Calendar Booking URL',
                        'type' => 'url',
                        'required' => true,
                        'placeholder' => 'https://calendar.google.com/calendar/appointments/...',
                        'description' => 'Your Google Calendar appointment booking URL'
                    ]
                ]
            ],
            self::PLATFORM_CUSTOM => [
                'label' => 'Custom Platform',
                'description' => 'Integrate with any custom booking platform',
                'icon' => 'cog-6-tooth',
                'fields' => [
                    'platform_name' => [
                        'label' => 'Platform Name',
                        'type' => 'text',
                        'required' => true,
                        'placeholder' => 'Acuity Scheduling, Bookly, etc.',
                        'description' => 'Name of your booking platform'
                    ],
                    'booking_url' => [
                        'label' => 'Booking URL',
                        'type' => 'url',
                        'required' => true,
                        'placeholder' => 'https://your-booking-platform.com/book',
                        'description' => 'Direct URL to your booking page'
                    ]
                ]
            ]
        ];
    }

    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
            self::STATUS_ERROR => 'Error',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForWorkspace($query, $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    public function scopeOrderByPriority($query)
    {
        return $query->orderBy('created_at', 'asc');
    }
} 