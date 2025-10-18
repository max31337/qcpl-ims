<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyRequest extends Model
{
    //
    protected $fillable = [
        'user_id',
        'items',
        'status',
        'admin_approved_at',
        'supply_officer_approved_at',
        'remarks',
    ];
}
