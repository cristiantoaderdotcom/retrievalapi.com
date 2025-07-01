<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EmailInbox extends Model
{
    use HasFactory;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'workspace_id',
        'name',
        'imap_host',
        'imap_port',
        'smtp_host',
        'smtp_port',
        'encryption',
        'validate_cert',
        'username',
        'password',
        'is_active',
        'is_verified',
        'start_date',
        'failed_attempts',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'validate_cert' => 'boolean',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'start_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot function from Laravel.
     */

    /**
     * Get the user that owns this inbox.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Get the processed emails for this inbox.
     */
    public function processedEmails(): HasMany
    {
        return $this->hasMany(ProcessedEmail::class);
    }

    /**
     * Get the connection configuration array for the IMAP client.
     */
    public function getImapConnectionConfig(): array
    {
        return [
            'host' => $this->imap_host,
            'port' => $this->imap_port,
            'encryption' => $this->encryption,
            'validate_cert' => $this->validate_cert,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }

    public function getSmtpConnectionConfig(): array
    {
        return [
            'host' => $this->smtp_host,
            'port' => $this->smtp_port,
            'encryption' => $this->encryption,
            'validate_cert' => $this->validate_cert,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }
}