<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Section extends Model
{
    use HasFactory;

    protected $fillable = ['name','code','division_id','is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function division(): BelongsTo { return $this->belongsTo(Division::class); }
}
