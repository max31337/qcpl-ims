<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserInvitation extends Model
{
    protected $fillable = [
        'email',
        'token',
        'invited_by',
        'status',
        'admin_notes',
        'expires_at',
        'registered_at',
        'approved_at',
        'user_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'registered_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function createInvitation(string $email, string $invitedBy, int $expiryHours = 72): self
    {
        return self::create([
            'email' => $email,
            'token' => Str::random(64),
            'invited_by' => $invitedBy,
            'expires_at' => Carbon::now()->addHours($expiryHours),
        ]);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    public function markAsRegistered(User $user): void
    {
        $this->update([
            'status' => 'registered',
            'registered_at' => now(),
            'user_id' => $user->id,
        ]);
    }

    public function approve(string $approvedBy): void
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        if ($this->user) {
            $this->user->update([
                'approval_status' => 'approved',
                'approved_at' => now(),
                'approved_by' => $approvedBy,
                'is_active' => true,
            ]);
        }
    }

    public function reject(string $reason): void
    {
        $this->update(['status' => 'rejected']);
        
        if ($this->user) {
            $this->user->update([
                'approval_status' => 'rejected',
                'rejection_reason' => $reason,
                'is_active' => false,
            ]);
        }
    }
}
