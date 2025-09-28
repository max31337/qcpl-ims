<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'firstname',
        'middlename',
        'lastname',
        'username',
        'email',
        'employee_id',
        'role',
        'branch_id',
        'division_id',
        'section_id',
        'is_active',
        'approval_status',
        'rejection_reason',
        'approved_at',
        'approved_by',
        'password',
        'mfa_enabled',
        'mfa_methods',
        'mfa_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'mfa_enabled' => 'boolean',
            'mfa_methods' => 'array',
            'mfa_verified_at' => 'datetime',
        ];
    }

    // Relationships
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function division(): BelongsTo { return $this->belongsTo(Division::class); }
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }

    // Role helpers
    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isStaff(): bool { return $this->role === 'staff'; }
    public function isSupplyOfficer(): bool { return $this->role === 'supply_officer'; }
    public function isPropertyOfficer(): bool { return $this->role === 'property_officer'; }
    public function isObserver(): bool { return $this->role === 'observer'; }
    public function isMainBranch(): bool { return (bool) optional($this->branch)->is_main; }

    // Approval status helpers
    public function isPending(): bool { return $this->approval_status === 'pending'; }
    public function isApproved(): bool { return $this->approval_status === 'approved'; }
    public function isRejected(): bool { return $this->approval_status === 'rejected'; }
    public function canLogin(): bool { return $this->isApproved() && $this->is_active; }

    // UserInvitation relationship
    public function invitation(): BelongsTo { return $this->belongsTo(UserInvitation::class); }

    // MFA relationship
    public function mfaCodes()
    {
        return $this->hasMany(MfaCode::class);
    }

    // MFA helpers
    public function hasMfaEnabled(): bool
    {
        return $this->mfa_enabled;
    }

    public function enableMfa(array $methods = ['email']): void
    {
        $this->update([
            'mfa_enabled' => true,
            'mfa_methods' => $methods,
        ]);
    }

    public function disableMfa(): void
    {
        $this->update([
            'mfa_enabled' => false,
            'mfa_methods' => null,
            'mfa_verified_at' => null,
        ]);
    }

    public function generateMfaCode(string $purpose = 'login'): MfaCode
    {
        // Clean up old codes for this user and purpose
        $this->mfaCodes()
            ->where('purpose', $purpose)
            ->where('used', false)
            ->update(['used' => true]);

        return MfaCode::createForUser($this, $purpose);
    }

    public function verifyMfaCode(string $code, string $purpose = 'login'): bool
    {
        $mfaCode = $this->mfaCodes()
            ->where('code', $code)
            ->where('purpose', $purpose)
            ->where('used', false)
            ->first();

        if (!$mfaCode || !$mfaCode->isValid()) {
            return false;
        }

        $mfaCode->markAsUsed();
        $this->update(['mfa_verified_at' => now()]);

        return true;
    }
}
