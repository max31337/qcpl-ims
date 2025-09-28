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

    // Get human-readable field name
    public function getHumanFieldName(string $field): string
    {
        $fieldMappings = [
            // Asset fields
            'property_number' => 'Property Number',
            'asset_group_id' => 'Asset Group',
            'description' => 'Description',
            'quantity' => 'Quantity',
            'date_acquired' => 'Date Acquired',
            'unit_cost' => 'Unit Cost',
            'total_cost' => 'Total Cost',
            'category_id' => 'Category',
            'status' => 'Status',
            'source' => 'Source',
            'image_path' => 'Image',
            'current_branch_id' => 'Current Branch',
            'current_division_id' => 'Current Division',
            'current_section_id' => 'Current Section',
            'created_by' => 'Created By',
            
            // Supply fields
            'code' => 'Supply Code',
            'stock_quantity' => 'Stock Quantity',
            'unit_price' => 'Unit Price',
            'minimum_stock' => 'Minimum Stock',
            'supplier' => 'Supplier',
            
            // User fields
            'name' => 'Name',
            'email' => 'Email',
            'role' => 'Role',
            'branch_id' => 'Branch',
            'division_id' => 'Division',
            'section_id' => 'Section',
            'is_active' => 'Active Status',
            'mfa_enabled' => 'MFA Enabled',
            
            // General fields
            'id' => 'ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];

        return $fieldMappings[$field] ?? ucwords(str_replace('_', ' ', $field));
    }

    // Get human-readable field value
    public function getHumanFieldValue(string $field, $value): string
    {
        if ($value === null || $value === '') {
            return '(empty)';
        }

        // Handle different field types
        switch ($field) {
            case 'status':
                return match($value) {
                    'active' => 'Active',
                    'condemned' => 'Condemned',
                    'disposed' => 'Disposed',
                    'under_repair' => 'Under Repair',
                    default => ucfirst($value),
                };

            case 'role':
                return match($value) {
                    'admin' => 'Administrator',
                    'property_officer' => 'Property Officer',
                    'supply_officer' => 'Supply Officer',
                    'staff' => 'Staff',
                    'observer' => 'Observer',
                    default => ucwords(str_replace('_', ' ', $value)),
                };

            case 'source':
                return match($value) {
                    'procurement' => 'Procurement',
                    'donation' => 'Donation',
                    'transfer' => 'Transfer',
                    'lease' => 'Lease',
                    default => ucfirst($value),
                };

            // ID fields - try to resolve to names
            case 'asset_group_id':
                $group = AssetGroup::find($value);
                return $group ? "#{$value} - {$group->description}" : "Asset Group #{$value}";

            case 'category_id':
                $category = Category::find($value);
                return $category ? "#{$value} - {$category->name}" : "Category #{$value}";

            case 'current_branch_id':
            case 'branch_id':
                $branch = Branch::find($value);
                return $branch ? "#{$value} - {$branch->name}" : "Branch #{$value}";

            case 'current_division_id':
            case 'division_id':
                $division = Division::find($value);
                return $division ? "#{$value} - {$division->name}" : "Division #{$value}";

            case 'current_section_id':
            case 'section_id':
                $section = Section::find($value);
                return $section ? "#{$value} - {$section->name}" : "Section #{$value}";

            case 'created_by':
                $user = User::find($value);
                return $user ? "#{$value} - {$user->name}" : "User #{$value}";

            // Boolean fields
            case 'is_active':
            case 'mfa_enabled':
                return $value ? 'Yes' : 'No';

            // Date fields
            case 'date_acquired':
            case 'created_at':
            case 'updated_at':
                if ($value instanceof \Carbon\Carbon) {
                    return $value->format('M d, Y g:i A');
                }
                return \Carbon\Carbon::parse($value)->format('M d, Y g:i A');

            // Cost fields
            case 'unit_cost':
            case 'total_cost':
            case 'unit_price':
                return '₱' . number_format((float)$value, 2);

            // Image fields
            case 'image_path':
                return $value ? basename($value) : '(no image)';

            default:
                return (string)$value;
        }
    }

    // Get formatted changes for display
    public function getFormattedChanges(): array
    {
        $changes = [];
        
        if (!$this->old_values || !$this->new_values) {
            return $changes;
        }

        // Get all changed fields
        $allFields = array_unique(array_merge(
            array_keys($this->old_values),
            array_keys($this->new_values)
        ));

        foreach ($allFields as $field) {
            $oldValue = $this->old_values[$field] ?? null;
            $newValue = $this->new_values[$field] ?? null;

            // Skip if values are the same
            if ($oldValue === $newValue) {
                continue;
            }

            // Skip system/internal fields that users don't care about
            if ($this->shouldSkipField($field)) {
                continue;
            }

            $changes[] = [
                'field' => $field,
                'field_name' => $this->getHumanFieldName($field),
                'old_value' => $this->getHumanFieldValue($field, $oldValue),
                'new_value' => $this->getHumanFieldValue($field, $newValue),
                'old_raw' => $oldValue,
                'new_raw' => $newValue,
            ];
        }

        return $changes;
    }

    // Get user-friendly summary of changes
    public function getChangesSummary(): string
    {
        if (!$this->old_values || !$this->new_values) {
            return '';
        }

        // Check if this looks like a deletion/disposal first
        $emptyCount = 0;
        $totalChanges = 0;
        foreach ($this->new_values as $field => $value) {
            if (!$this->shouldSkipField($field)) {
                $totalChanges++;
                if (empty($value)) {
                    $emptyCount++;
                }
            }
        }

        // If record was disposed/deleted, just show the status change
        if (isset($this->old_values['status']) && isset($this->new_values['status'])) {
            $oldStatus = $this->getHumanFieldValue('status', $this->old_values['status']);
            $newStatus = $this->getHumanFieldValue('status', $this->new_values['status']);
            
            // If status changed to disposed/deleted and most fields became empty
            if (in_array(strtolower($newStatus), ['disposed', 'deleted']) && $emptyCount > $totalChanges * 0.7) {
                return "Asset was {$newStatus} - all data cleared from system";
            } elseif ($oldStatus !== $newStatus) {
                return "Status: {$oldStatus} → {$newStatus}";
            }
        }

        // If mostly empty, it's a deletion
        if ($emptyCount > $totalChanges * 0.8) {
            return "Record was deleted or archived - all data removed";
        }

        $summaryParts = [];

        // Check for meaningful changes (only if not a deletion)
        if (isset($this->old_values['current_branch_id']) && isset($this->new_values['current_branch_id'])) {
            $oldBranch = $this->getHumanFieldValue('current_branch_id', $this->old_values['current_branch_id']);
            $newBranch = $this->getHumanFieldValue('current_branch_id', $this->new_values['current_branch_id']);
            if ($oldBranch !== $newBranch && !empty($this->new_values['current_branch_id'])) {
                $summaryParts[] = "Moved to: {$newBranch}";
            }
        }

        if (isset($this->old_values['description']) && isset($this->new_values['description']) && !empty($this->new_values['description'])) {
            $summaryParts[] = "Description updated";
        }

        if (isset($this->old_values['unit_cost']) && isset($this->new_values['unit_cost']) && !empty($this->new_values['unit_cost'])) {
            $oldCost = $this->getHumanFieldValue('unit_cost', $this->old_values['unit_cost']);
            $newCost = $this->getHumanFieldValue('unit_cost', $this->new_values['unit_cost']);
            $summaryParts[] = "Cost: {$oldCost} → {$newCost}";
        }

        return implode(', ', $summaryParts) ?: "Multiple fields updated";
    }

    // Determine if a field should be skipped from user display
    private function shouldSkipField(string $field): bool
    {
        // Always skip these system fields
        $alwaysSkipFields = [
            'id',                    // Internal ID
            'created_at',           // System timestamp
            'updated_at',           // System timestamp
            'created_by',           // Usually not changed
            'asset_group_id',       // Internal relationship
        ];

        if (in_array($field, $alwaysSkipFields)) {
            return true;
        }

        // For disposal/deletion operations, be very restrictive
        if ($this->isDisposalOperation()) {
            $importantFields = [
                'status',           // The main status change
            ];
            return !in_array($field, $importantFields);
        }

        return false;
    }

    // Check if this is a disposal/deletion operation
    private function isDisposalOperation(): bool
    {
        if (!$this->old_values || !$this->new_values) {
            return false;
        }

        // Check if status changed to disposed/deleted
        if (isset($this->old_values['status']) && isset($this->new_values['status'])) {
            $newStatus = strtolower($this->new_values['status']);
            if (in_array($newStatus, ['disposed', 'deleted'])) {
                return true;
            }
        }

        // Check if most fields became empty (indicating deletion)
        $emptyCount = 0;
        $totalChanges = 0;
        foreach ($this->new_values as $field => $value) {
            if (!in_array($field, ['id', 'created_at', 'updated_at', 'created_by', 'asset_group_id'])) {
                $totalChanges++;
                if (empty($value)) {
                    $emptyCount++;
                }
            }
        }

        return $emptyCount > $totalChanges * 0.7; // More than 70% of fields became empty
    }

    // Get all changes including system fields (for raw view)
    public function getAllChanges(): array
    {
        $changes = [];
        
        if (!$this->old_values || !$this->new_values) {
            return $changes;
        }

        // Get all changed fields (including system fields)
        $allFields = array_unique(array_merge(
            array_keys($this->old_values),
            array_keys($this->new_values)
        ));

        foreach ($allFields as $field) {
            $oldValue = $this->old_values[$field] ?? null;
            $newValue = $this->new_values[$field] ?? null;

            // Skip if values are the same
            if ($oldValue === $newValue) {
                continue;
            }

            $changes[] = [
                'field' => $field,
                'field_name' => $this->getHumanFieldName($field),
                'old_value' => $this->getHumanFieldValue($field, $oldValue),
                'new_value' => $this->getHumanFieldValue($field, $newValue),
                'old_raw' => $oldValue,
                'new_raw' => $newValue,
                'is_system_field' => $this->shouldSkipField($field),
            ];
        }

        return $changes;
    }
}
