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
}
