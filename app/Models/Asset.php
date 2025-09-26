<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_number','description','quantity','date_acquired','unit_cost','total_cost','category_id',
        'status','source','image_path','current_branch_id','current_division_id','current_section_id','created_by'
    ];

    protected $casts = [
        'date_acquired' => 'date',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function currentBranch(): BelongsTo { return $this->belongsTo(Branch::class, 'current_branch_id'); }
    public function currentDivision(): BelongsTo { return $this->belongsTo(Division::class, 'current_division_id'); }
    public function currentSection(): BelongsTo { return $this->belongsTo(Section::class, 'current_section_id'); }
    public function transferHistories(): HasMany { return $this->hasMany(AssetTransferHistory::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function scopeForUser($query, User $user)
    {
        if ($user->isMainBranch() && ($user->isAdmin() || $user->isObserver())) {
            return $query;
        }
        return $query->where('current_branch_id', $user->branch_id);
    }

    public static function generatePropertyNumber(): string
    {
        $year = date('Y');
        $last = static::where('property_number', 'like', $year.'-%')
            ->orderBy('property_number', 'desc')->first();
        $n = $last ? (int) substr($last->property_number, 5) + 1 : 1;
        return $year.'-'.str_pad((string)$n, 4, '0', STR_PAD_LEFT);
    }
}
