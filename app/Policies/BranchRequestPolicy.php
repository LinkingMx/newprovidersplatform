<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BranchRequest;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class BranchRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BranchRequest');
    }

    public function view(AuthUser $authUser, BranchRequest $branchRequest): bool
    {
        return $authUser->can('View:BranchRequest');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BranchRequest');
    }

    public function update(AuthUser $authUser, BranchRequest $branchRequest): bool
    {
        return $authUser->can('Update:BranchRequest');
    }

    public function delete(AuthUser $authUser, BranchRequest $branchRequest): bool
    {
        return $authUser->can('Delete:BranchRequest');
    }

    public function restore(AuthUser $authUser, BranchRequest $branchRequest): bool
    {
        return $authUser->can('Restore:BranchRequest');
    }

    public function forceDelete(AuthUser $authUser, BranchRequest $branchRequest): bool
    {
        return $authUser->can('ForceDelete:BranchRequest');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BranchRequest');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BranchRequest');
    }

    public function replicate(AuthUser $authUser, BranchRequest $branchRequest): bool
    {
        return $authUser->can('Replicate:BranchRequest');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BranchRequest');
    }
}
