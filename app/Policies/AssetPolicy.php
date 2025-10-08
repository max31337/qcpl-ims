<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AssetPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view assets (with branch scoping applied in the model)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Asset $asset): bool
    {
        // Users can view assets based on branch access
        if ($user->isMainBranch() && ($user->isAdmin() || $user->isObserver() || $user->isPropertyOfficer())) {
            return true; // Can see all assets
        }
        
        return $asset->current_branch_id === $user->branch_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Admin, Staff, and Property Officers can create assets
        return in_array($user->role, ['admin', 'staff', 'property_officer']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Asset $asset): bool
    {
        // Admin can update any asset
        if ($user->isAdmin()) {
            return true;
        }
        
        // Main library property officers can update any asset (global access)
        if ($user->isPropertyOfficer() && $user->isMainBranch()) {
            return true;
        }
        
        // Staff and Property Officers can update assets in their branch
        if (in_array($user->role, ['staff', 'property_officer'])) {
            return $asset->current_branch_id === $user->branch_id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Asset $asset): bool
    {
        // Only Admin can delete assets
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can transfer the model to another location.
     */
    public function transfer(User $user, Asset $asset): bool
    {
        // Admin can transfer any asset
        if ($user->isAdmin()) {
            return true;
        }
        
        // Main library property officers can transfer any asset (global access)
        if ($user->isPropertyOfficer() && $user->isMainBranch()) {
            return true;
        }
        
        // Staff and Property Officers can transfer assets from their branch
        if (in_array($user->role, ['staff', 'property_officer'])) {
            return $asset->current_branch_id === $user->branch_id;
        }
        
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Asset $asset): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Asset $asset): bool
    {
        return $user->isAdmin();
    }
}
