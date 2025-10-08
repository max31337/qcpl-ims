<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetTransferHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id','transfer_date','origin_branch_id','origin_division_id','origin_section_id',
        'previous_branch_id','previous_division_id','previous_section_id',
        'current_branch_id','current_division_id','current_section_id','remarks','transferred_by'
    ];

    protected $casts = ['transfer_date' => 'date'];

    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
    public function transferredBy(): BelongsTo { return $this->belongsTo(User::class, 'transferred_by'); }

    // Origin relations (original/default location)
    public function originBranch(): BelongsTo { return $this->belongsTo(Branch::class, 'origin_branch_id'); }
    public function originDivision(): BelongsTo { return $this->belongsTo(Division::class, 'origin_division_id'); }
    public function originSection(): BelongsTo { return $this->belongsTo(Section::class, 'origin_section_id'); }

    // Previous relations (from location before this transfer)
    public function previousBranch(): BelongsTo { return $this->belongsTo(Branch::class, 'previous_branch_id'); }
    public function previousDivision(): BelongsTo { return $this->belongsTo(Division::class, 'previous_division_id'); }
    public function previousSection(): BelongsTo { return $this->belongsTo(Section::class, 'previous_section_id'); }

    // Current relations (to location after this transfer)
    public function currentBranch(): BelongsTo { return $this->belongsTo(Branch::class, 'current_branch_id'); }
    public function currentDivision(): BelongsTo { return $this->belongsTo(Division::class, 'current_division_id'); }
    public function currentSection(): BelongsTo { return $this->belongsTo(Section::class, 'current_section_id'); }

    public function scopeForUser($query, User $user)
    {
        // Main-branch users (admin, observer, property_officer) see all transfer histories
        if ($user->isMainBranch() && ($user->isAdmin() || $user->isObserver() || $user->isPropertyOfficer())) {
            return $query;
        }
        
        // Others see transfers where they're involved (origin or current branch)
        return $query->where(function ($q) use ($user) {
            $q->where('origin_branch_id', $user->branch_id)
              ->orWhere('current_branch_id', $user->branch_id);
        });
    }
}
