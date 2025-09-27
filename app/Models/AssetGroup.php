<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'description','category_id','date_acquired','unit_cost','status','source','image_path','created_by'
    ];

    public function assets(): HasMany { return $this->hasMany(Asset::class); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
