<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvitationRequest extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'department',
        'reason',
        'status',
        'admin_notes',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function approve(User $reviewer, ?string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'admin_notes' => $notes,
        ]);
    }

    public function reject(User $reviewer, ?string $notes = null): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'admin_notes' => $notes,
        ]);
    }

    /**
     * Check if email is a valid government email
     */
    public static function isGovernmentEmail(string $email): bool
    {
        $governmentDomains = [
            'gov.ph',
            'qc.gov.ph',
            'quezoncity.gov.ph',
            'deped.gov.ph',
            'dilg.gov.ph',
            'doh.gov.ph',
            'dti.gov.ph',
            'dof.gov.ph',
            'doe.gov.ph',
            'da.gov.ph',
            'denr.gov.ph',
            'dpwh.gov.ph',
            'dict.gov.ph',
            'dost.gov.ph',
            'dole.gov.ph',
            'dswd.gov.ph',
            'dnd.gov.ph',
            'dfa.gov.ph',
            'doj.gov.ph',
            'dbm.gov.ph',
            'neda.gov.ph',
            'csc.gov.ph',
            'coa.gov.ph',
            // Add more government domains as needed
        ];

        $emailDomain = strtolower(substr(strrchr($email, '@'), 1));
        
        foreach ($governmentDomains as $domain) {
            if ($emailDomain === $domain || str_ends_with($emailDomain, '.' . $domain)) {
                return true;
            }
        }

        return false;
    }
}
