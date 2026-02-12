<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\SupplierResource;
use App\Models\SupplierInvitation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        // Generate invitation and send email
        $this->createAndSendInvitation($record);

        return $record;
    }

    protected function createAndSendInvitation(Model $supplier): void
    {
        $token = bin2hex(random_bytes(32));

        SupplierInvitation::create([
            'supplier_id' => $supplier->id,
            'token' => $token,
            'sent_at' => now(),
            'expires_at' => now()->addDays(7),
        ]);

        // TODO: Send invitation email
        // SupplierInvitationMailable::dispatch($supplier, $token);

        $supplier->update(['status' => 'invited']);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
