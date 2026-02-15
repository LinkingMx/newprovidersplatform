<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Supplier;
use App\Models\SupplierDocument;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SupplierDocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Admin panel: check Shield permissions.
     */
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SupplierDocument');
    }

    public function view(AuthUser $authUser, SupplierDocument $supplierDocument): bool
    {
        return $authUser->can('View:SupplierDocument');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SupplierDocument');
    }

    public function update(AuthUser $authUser, SupplierDocument $supplierDocument): bool
    {
        return $authUser->can('Update:SupplierDocument');
    }

    public function delete(AuthUser $authUser, SupplierDocument $supplierDocument): bool
    {
        return $authUser->can('Delete:SupplierDocument');
    }

    public function restore(AuthUser $authUser, SupplierDocument $supplierDocument): bool
    {
        return $authUser->can('Restore:SupplierDocument');
    }

    public function forceDelete(AuthUser $authUser, SupplierDocument $supplierDocument): bool
    {
        return $authUser->can('ForceDelete:SupplierDocument');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SupplierDocument');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SupplierDocument');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SupplierDocument');
    }

    /**
     * Supplier-side: verify document ownership for upload.
     */
    public function upload(Supplier $supplier, SupplierDocument $supplierDocument): bool
    {
        return $supplier->id === $supplierDocument->supplier_id
            && in_array($supplierDocument->document_state_id, [1, 4]);
    }

    /**
     * Supplier-side: verify document ownership for download.
     */
    public function download(Supplier $supplier, SupplierDocument $supplierDocument): bool
    {
        return $supplier->id === $supplierDocument->supplier_id;
    }
}
