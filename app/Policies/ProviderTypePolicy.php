<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\ProviderType;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProviderTypePolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:ProviderType');
    }

    public function view(AuthUser $authUser, ProviderType $providerType): bool
    {
        return $authUser->can('View:ProviderType');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:ProviderType');
    }

    public function update(AuthUser $authUser, ProviderType $providerType): bool
    {
        return $authUser->can('Update:ProviderType');
    }

    public function delete(AuthUser $authUser, ProviderType $providerType): bool
    {
        return $authUser->can('Delete:ProviderType');
    }

    public function restore(AuthUser $authUser, ProviderType $providerType): bool
    {
        return $authUser->can('Restore:ProviderType');
    }

    public function forceDelete(AuthUser $authUser, ProviderType $providerType): bool
    {
        return $authUser->can('ForceDelete:ProviderType');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:ProviderType');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:ProviderType');
    }

    public function replicate(AuthUser $authUser, ProviderType $providerType): bool
    {
        return $authUser->can('Replicate:ProviderType');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:ProviderType');
    }

}