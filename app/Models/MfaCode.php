<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class MfaCode extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'type',
        'expires_at',
        'used',
        'purpose',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    public function isValid(): bool
    {
        return !$this->used && !$this->isExpired();
    }

    public static function generateCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function createForUser(User $user, string $purpose = 'login', int $expiresInMinutes = 10): self
    {
        return self::create([
            'user_id' => $user->id,
            'code' => self::generateCode(),
            'type' => 'email',
            'expires_at' => now()->addMinutes($expiresInMinutes),
            'purpose' => $purpose,
        ]);
    }

    public function markAsUsed(): void
    {
        $this->update(['used' => true]);
    }
}
