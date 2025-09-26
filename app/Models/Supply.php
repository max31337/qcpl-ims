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
}
