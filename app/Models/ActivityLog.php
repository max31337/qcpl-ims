<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ActivityLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'user_id','action','model','model_id','old_values','new_values','description',
        'ip_address','user_agent','browser','browser_version','platform','device','session_id','request_data','created_at'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'request_data' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Get the related model instance
    public function getRelatedModel()
    {
        if (!$this->model || !$this->model_id) {
            return null;
        }

        $modelClass = "App\\Models\\{$this->model}";
        
        if (class_exists($modelClass)) {
            return $modelClass::find($this->model_id);
        }

        return null;
    }

    // Static method to log activity
    public static function log(
        string $action,
        $model = null,
        array $oldValues = [],
        array $newValues = [],
        string $description = null,
        $userId = null,
        bool $includeSecurityContext = false
    ): self {
        $userId = $userId ?? auth()->id();
        
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'model' => $model ? class_basename($model) : null,
            'model_id' => $model ? $model->id : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description ?? self::generateDescription($action, $model, $oldValues, $newValues),
            'created_at' => now(),
        ];

        // Add security context if requested (mainly for login/logout events)
        if ($includeSecurityContext) {
            $securityContext = self::getSecurityContext();
            $data = array_merge($data, $securityContext);
        }
        
        return self::create($data);
    }

    // Get security context from current request
    private static function getSecurityContext(): array
    {
        $request = request();
        $agent = new \Jenssegers\Agent\Agent();
        
        return [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'browser' => $agent->browser(),
            'browser_version' => $agent->version($agent->browser()),
            'platform' => $agent->platform(),
            'device' => $agent->deviceType(),
            'session_id' => session()->getId(),
            'request_data' => [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'referer' => $request->header('referer'),
                'timestamp' => now()->toISOString(),
                'is_mobile' => $agent->isMobile(),
                'is_tablet' => $agent->isTablet(),
                'is_desktop' => $agent->isDesktop(),
                'is_robot' => $agent->isRobot(),
            ]
        ];
    }

    // Generate automatic description
    private static function generateDescription(string $action, $model, array $oldValues, array $newValues): string
    {
        $userName = auth()->user()->name ?? 'System';
        $modelName = $model ? class_basename($model) : 'record';
        $modelId = $model ? $model->id : 'unknown';

        return match($action) {
            'created' => "{$userName} created {$modelName} #{$modelId}",
            'updated' => "{$userName} updated {$modelName} #{$modelId}",
            'deleted' => "{$userName} deleted {$modelName} #{$modelId}",
            'transferred' => "{$userName} transferred {$modelName} #{$modelId}",
            'login' => "{$userName} logged in",
            'logout' => "{$userName} logged out",
            'password_changed' => "{$userName} changed their password",
            'mfa_enabled' => "{$userName} enabled multi-factor authentication",
            'mfa_disabled' => "{$userName} disabled multi-factor authentication",
            'profile_updated' => "{$userName} updated their profile",
            'export' => "{$userName} exported {$modelName} data",
            'import' => "{$userName} imported {$modelName} data",
            default => "{$userName} performed {$action} on {$modelName} #{$modelId}",
        };
    }

    // Scope for recent activities
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Scope for specific user
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Scope for specific model
    public function scopeForModel($query, string $model, int $modelId = null)
    {
        $query->where('model', class_basename($model));
        
        if ($modelId) {
            $query->where('model_id', $modelId);
        }
        
        return $query;
    }

    // Get action icon
    public function getActionIconAttribute(): string
    {
        return match($this->action) {
            'created' => 'plus',
            'updated' => 'pencil',
            'deleted' => 'trash-2',
            'transferred' => 'transfer',
            'login' => 'log-in',
            'logout' => 'log-out',
            'password_changed' => 'shield-check',
            'mfa_enabled', 'mfa_disabled' => 'shield-check',
            'profile_updated' => 'user',
            'export' => 'download',
            'import' => 'upload',
            default => 'activity',
        };
    }

    // Get action color
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            'created' => 'text-green-600',
            'updated' => 'text-blue-600',
            'deleted' => 'text-red-600',
            'transferred' => 'text-purple-600',
            'login' => 'text-green-600',
            'logout' => 'text-gray-600',
            'password_changed', 'mfa_enabled', 'mfa_disabled' => 'text-orange-600',
            'profile_updated' => 'text-blue-600',
            'export', 'import' => 'text-indigo-600',
            default => 'text-gray-600',
        };
    }
}
