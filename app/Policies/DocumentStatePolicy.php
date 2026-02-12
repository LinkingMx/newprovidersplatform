<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DocumentState;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentStatePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DocumentState');
    }

    public function view(AuthUser $authUser, DocumentState $documentState): bool
    {
        return $authUser->can('View:DocumentState');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DocumentState');
    }

    public function update(AuthUser $authUser, DocumentState $documentState): bool
    {
        return $authUser->can('Update:DocumentState');
    }

    public function delete(AuthUser $authUser, DocumentState $documentState): bool
    {
        return $authUser->can('Delete:DocumentState');
    }

    public function restore(AuthUser $authUser, DocumentState $documentState): bool
    {
        return $authUser->can('Restore:DocumentState');
    }

    public function forceDelete(AuthUser $authUser, DocumentState $documentState): bool
    {
        return $authUser->can('ForceDelete:DocumentState');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DocumentState');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DocumentState');
    }

    public function replicate(AuthUser $authUser, DocumentState $documentState): bool
    {
        return $authUser->can('Replicate:DocumentState');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DocumentState');
    }

}