<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Enums\SupplierStatus;
use App\Filament\Resources\Suppliers\SupplierResource;
use App\Mail\SupplierInvitationMailable;
use App\Models\DocumentState;
use App\Models\Supplier;
use App\Models\SupplierDocument;
use App\Models\SupplierInvitation;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $record = parent::handleRecordCreation($data);

        // Generate invitation and send email
        $this->createAndSendInvitation($record);

        // Auto-assign documents based on provider type
        $this->assignDocuments($record);

        return $record;
    }

    protected function createAndSendInvitation(Model $supplier): void
    {
        $token = bin2hex(random_bytes(32));

        $invitation = SupplierInvitation::create([
            'supplier_id' => $supplier->id,
            'token' => $token,
            'expires_at' => now()->addDays(7),
        ]);

        // Send invitation email synchronously to ensure delivery
        Mail::to($supplier->email)->send(
            new SupplierInvitationMailable($supplier, $invitation)
        );

        $invitation->update(['sent_at' => now()]);

        $supplier->update(['status' => SupplierStatus::Invited]);
    }

    protected function assignDocuments(Model $supplier): void
    {
        /** @var Supplier $supplier */
        $providerType = $supplier->providerType;

        if (! $providerType) {
            return;
        }

        $documentTypes = $providerType->documentTypes;

        if ($documentTypes->isEmpty()) {
            return;
        }

        $defaultStateId = DocumentState::where('por_defecto', true)->value('id') ?? 1;
        $created = 0;

        foreach ($documentTypes as $documentType) {
            SupplierDocument::create([
                'supplier_id' => $supplier->id,
                'document_type_id' => $documentType->id,
                'document_state_id' => $defaultStateId,
            ]);

            $created++;
        }

        // Documents email is not sent during creation — the supplier
        // receives the invitation email first to set their password.
        // The documents email is sent only when assigned manually later.
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
