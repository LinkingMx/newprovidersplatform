<?php

namespace App\Filament\Resources\Suppliers\Actions;

use App\Mail\SupplierDocumentsAssignedMailable;
use App\Models\DocumentState;
use App\Models\Supplier;
use App\Models\SupplierDocument;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class AsignarDocumentosAction
{
    public static function make(): Action
    {
        return Action::make('asignarDocumentos')
            ->label('Asignar Documentos')
            ->icon('heroicon-o-document-plus')
            ->color('primary')
            ->requiresConfirmation()
            ->modalHeading('Asignar Tipos de Documento')
            ->modalDescription('Se asignarán los tipos de documento configurados para el tipo de proveedor. Los documentos ya asignados serán ignorados.')
            ->modalSubmitActionLabel('Asignar')
            ->visible(fn (Supplier $record): bool => $record->provider_type_id !== null)
            ->action(function (Supplier $record): void {
                $providerType = $record->providerType;

                if (! $providerType) {
                    Notification::make()
                        ->danger()
                        ->icon('heroicon-o-x-circle')
                        ->title('Error')
                        ->body('El proveedor no tiene un tipo de proveedor asignado')
                        ->send();

                    return;
                }

                $documentTypes = $providerType->documentTypes;

                if ($documentTypes->isEmpty()) {
                    Notification::make()
                        ->warning()
                        ->icon('heroicon-o-exclamation-triangle')
                        ->title('Sin Documentos')
                        ->body('El tipo de proveedor no tiene tipos de documento configurados')
                        ->send();

                    return;
                }

                $defaultState = DocumentState::where('por_defecto', true)->first();
                $created = 0;
                $skipped = 0;

                foreach ($documentTypes as $documentType) {
                    $exists = SupplierDocument::where('supplier_id', $record->id)
                        ->where('document_type_id', $documentType->id)
                        ->exists();

                    if ($exists) {
                        $skipped++;

                        continue;
                    }

                    SupplierDocument::create([
                        'supplier_id' => $record->id,
                        'document_type_id' => $documentType->id,
                        'document_state_id' => $defaultState?->id ?? 1,
                    ]);

                    $created++;
                }

                $message = "{$created} documento(s) asignado(s)";
                if ($skipped > 0) {
                    $message .= ", {$skipped} ya existente(s)";
                }

                if ($created > 0) {
                    Mail::to($record->email)->send(
                        new SupplierDocumentsAssignedMailable($record, $created)
                    );
                }

                Notification::make()
                    ->success()
                    ->icon('heroicon-o-check-circle')
                    ->title('Documentos Asignados')
                    ->body($message)
                    ->send();
            });
    }
}
