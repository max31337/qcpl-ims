<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Supply extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_number','description','category_id','current_stock','min_stock','unit_cost','status','branch_id','created_by','last_updated'
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    // Scope supplies according to user's visibility rules.
    public function scopeForUser($query, User $user)
    {
        // Main-branch admins/observers see all; others are branch-scoped
        if ($user->isAdmin() || $user->isObserver() || $user->isMainBranch()) {
            return $query; // global scope
        }

        return $query->where('branch_id', $user->branch_id);
    }

    // Generate sequential supply number like SUP-001
    public static function generateSupplyNumber(): string
    {
        $prefix = 'SUP-';
        $last = self::where('supply_number', 'like', $prefix.'%')
            ->orderBy('supply_number', 'desc')
            ->first();

        if ($last) {
            $num = (int) str_pad(preg_replace('/\D/', '', $last->supply_number), 3, '0', STR_PAD_LEFT);
            $next = $num + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }
}
