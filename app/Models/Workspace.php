<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\HasDynamicSettings;

class Workspace extends Model
{
    use HasDynamicSettings;

    protected $fillable = [
        'uuid',
        'name',
        'language_id',
        'daily_loads',
        'is_active',
        'user_id',
    ];

    /**
     * Get the user that owns the workspace.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(WorkspaceSetting::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function loads(): HasMany
    {
        return $this->hasMany(WorkspaceLoad::class);
    }

    public function knowledgeBases(): HasMany
    {
        return $this->hasMany(KnowledgeBase::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(KnowledgeBaseResource::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function emailInbox(): HasOne
    {
        return $this->hasOne(EmailInbox::class);
    }

    public function facebookPage(): HasOne
    {
        return $this->hasOne(FacebookPage::class);
    }

    public function instagramPage(): HasOne
    {
        return $this->hasOne(InstagramPage::class);
    }

    public function telegramBot(): HasOne
    {
        return $this->hasOne(TelegramBot::class);
    }

    public function discordBot(): HasOne
    {
        return $this->hasOne(DiscordBot::class);
    }
} 