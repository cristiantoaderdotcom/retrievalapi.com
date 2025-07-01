<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomApiIntegration extends Model
{
    use HasFactory;

    const ACTION_GET_DATA = 'get_data';
    const ACTION_SUBMIT_DATA = 'submit_data';

    const AUTH_NONE = 'none';
    const AUTH_BEARER = 'bearer';
    const AUTH_API_KEY = 'api_key';
    const AUTH_BASIC = 'basic';
    const AUTH_CUSTOM = 'custom';

    const HTTP_GET = 'GET';
    const HTTP_POST = 'POST';
    const HTTP_PUT = 'PUT';
    const HTTP_DELETE = 'DELETE';
    const HTTP_PATCH = 'PATCH';

    protected $fillable = [
        'workspace_id',
        'name',
        'description',
        'api_url',
        'http_method',
        'auth_type',
        'auth_config',
        'input_schema',
        'trigger_keywords',
        'ai_rules',
        'action_type',
        'is_active',
        'confirmation_message',
        'success_response',
        'headers',
        'timeout',
    ];

    protected $casts = [
        'auth_config' => 'array',
        'input_schema' => 'array',
        'trigger_keywords' => 'array',
        'ai_rules' => 'array',
        'success_response' => 'array',
        'headers' => 'array',
        'is_active' => 'boolean',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function customApiRequests(): HasMany
    {
        return $this->hasMany(CustomApiRequest::class);
    }

    public function getActionTypeLabelAttribute(): string
    {
        return match ($this->action_type) {
            self::ACTION_GET_DATA => 'Get Data',
            self::ACTION_SUBMIT_DATA => 'Submit Data',
            default => 'Unknown',
        };
    }

    public function getAuthTypeLabelAttribute(): string
    {
        return match ($this->auth_type) {
            self::AUTH_NONE => 'No Authentication',
            self::AUTH_BEARER => 'Bearer Token',
            self::AUTH_API_KEY => 'API Key',
            self::AUTH_BASIC => 'Basic Authentication',
            self::AUTH_CUSTOM => 'Custom Headers',
            default => 'Unknown',
        };
    }

    public function getHttpMethodLabelAttribute(): string
    {
        return strtoupper($this->http_method);
    }

    public function getTriggerKeywordsStringAttribute(): string
    {
        return is_array($this->trigger_keywords) ? implode(', ', $this->trigger_keywords) : '';
    }

    public static function getActionTypeOptions(): array
    {
        return [
            self::ACTION_GET_DATA => 'Get Data (Retrieve information from API)',
            self::ACTION_SUBMIT_DATA => 'Submit Data (Send information to API)',
        ];
    }

    public static function getAuthTypeOptions(): array
    {
        return [
            self::AUTH_NONE => 'No Authentication',
            self::AUTH_BEARER => 'Bearer Token',
            self::AUTH_API_KEY => 'API Key',
            self::AUTH_BASIC => 'Basic Authentication (Username/Password)',
            self::AUTH_CUSTOM => 'Custom Headers',
        ];
    }

    public static function getHttpMethodOptions(): array
    {
        return [
            self::HTTP_GET => 'GET',
            self::HTTP_POST => 'POST',
            self::HTTP_PUT => 'PUT',
            self::HTTP_PATCH => 'PATCH',
            self::HTTP_DELETE => 'DELETE',
        ];
    }

    public static function getFieldTypeOptions(): array
    {
        return [
            'text' => 'Text',
            'email' => 'Email',
            'number' => 'Number',
            'textarea' => 'Textarea',
            'select' => 'Select Dropdown',
            'checkbox' => 'Checkbox',
            'date' => 'Date',
            'url' => 'URL',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForWorkspace($query, $workspaceId)
    {
        return $query->where('workspace_id', $workspaceId);
    }

    public function scopeByActionType($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    public function getAuthHeaders(): array
    {
        $headers = [];

        switch ($this->auth_type) {
            case self::AUTH_BEARER:
                if ($token = data_get($this->auth_config, 'token')) {
                    $headers['Authorization'] = 'Bearer ' . $token;
                }
                break;

            case self::AUTH_API_KEY:
                $key = data_get($this->auth_config, 'key');
                $value = data_get($this->auth_config, 'value');
                $location = data_get($this->auth_config, 'location', 'header'); // header, query

                if ($key && $value && $location === 'header') {
                    $headers[$key] = $value;
                }
                break;

            case self::AUTH_BASIC:
                $username = data_get($this->auth_config, 'username');
                $password = data_get($this->auth_config, 'password');
                if ($username && $password) {
                    $headers['Authorization'] = 'Basic ' . base64_encode($username . ':' . $password);
                }
                break;

            case self::AUTH_CUSTOM:
                $customHeaders = data_get($this->auth_config, 'headers', []);
                foreach ($customHeaders as $header) {
                    if (isset($header['key']) && isset($header['value'])) {
                        $headers[$header['key']] = $header['value'];
                    }
                }
                break;
        }

        return $headers;
    }

    public function getQueryParams($data = []): array
    {
        $params = [];

        // Add API key to query if configured
        if ($this->auth_type === self::AUTH_API_KEY) {
            $key = data_get($this->auth_config, 'key');
            $value = data_get($this->auth_config, 'value');
            $location = data_get($this->auth_config, 'location', 'header');

            if ($key && $value && $location === 'query') {
                $params[$key] = $value;
            }
        }

        // Add data as query params for GET requests
        if ($this->http_method === self::HTTP_GET) {
            $params = array_merge($params, $data);
        }

        return $params;
    }
}
