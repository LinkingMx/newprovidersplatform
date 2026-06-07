<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\SupplierDocumentStatusMailable;
use App\Models\DocumentState;
use App\Models\SupplierDocument;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

/**
 * Lógica de revisión de documentos por admin: aprobar, rechazar y cambio libre de estado.
 *
 * - Valida que la transición esté permitida según `transiciones_permitidas` del estado actual.
 * - Persiste reviewed_at + reviewed_by + notas.
 * - Dispara email al proveedor cuando el resultado es APROBADO o RECHAZADO.
 *
 * Reutilizado por:
 * - DocumentReviewResource (vista global de revisión masiva)
 * - DocumentsRelationManager (revisión 1×1 desde la ficha del proveedor)
 */
class DocumentReviewService
{
    public function approve(SupplierDocument $document, ?string $notas = null): void
    {
        $this->transitionTo($document, DocumentState::APROBADO, $notas);
    }

    public function reject(SupplierDocument $document, string $motivo): void
    {
        $this->transitionTo($document, DocumentState::RECHAZADO, $motivo);
    }

    public function changeState(SupplierDocument $document, int $newStateId, ?string $notas = null): void
    {
        $this->transitionTo($document, $newStateId, $notas);
    }

    private function transitionTo(SupplierDocument $document, int $newStateId, ?string $notas): void
    {
        $newState = DocumentState::find($newStateId);

        if (! $newState) {
            throw new InvalidArgumentException("Estado #{$newStateId} no existe.");
        }

        // Si el documento aún no tiene estado cargado, recargar.
        $currentState = $document->documentState ?? DocumentState::find($document->document_state_id);

        if ($currentState) {
            $allowed = $currentState->transiciones_permitidas ?? [];
            if (! empty($allowed) && ! in_array($newState->nombre, $allowed, true)) {
                throw new InvalidArgumentException(
                    "Transición no permitida: {$currentState->nombre} → {$newState->nombre}"
                );
            }
        }

        $document->update([
            'document_state_id' => $newStateId,
            'notas' => $notas,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        if (in_array($newState->nombre, ['Aprobado', 'Rechazado'], true)) {
            $document->load(['supplier', 'documentType']);

            Mail::to($document->supplier->email)->send(
                new SupplierDocumentStatusMailable($document->supplier, $document, $newState)
            );
        }
    }
}
