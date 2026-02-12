<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\SupplierResource;
use App\Jobs\SendSupplierInvitationEmail;
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
            'expires_at' => now()->addDays(7),
        ]);

        // Dispatch job to send invitation email
        SendSupplierInvitationEmail::dispatch($supplier);

        $supplier->update(['status' => 'invited']);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
