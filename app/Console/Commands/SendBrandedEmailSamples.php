<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Mail\BranchRequestResolvedMailable;
use App\Mail\SupplierDocumentsAssignedMailable;
use App\Mail\SupplierDocumentStatusMailable;
use App\Mail\SupplierInvitationMailable;
use App\Mail\SupplierPasswordResetMailable;
use App\Models\BranchRequest;
use App\Models\DocumentState;
use App\Models\Supplier;
use App\Models\SupplierDocument;
use App\Models\SupplierInvitation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Envía una muestra de cada uno de los 5 emails branded a una dirección
 * para inspeccionar el render en el inbox de Mailtrap (o el mailer configurado).
 *
 * Usa registros existentes de la BD (primero disponible). No persiste cambios.
 */
class SendBrandedEmailSamples extends Command
{
    protected $signature = 'email:samples {--to=armingkings@gmail.com : Dirección destino}';

    protected $description = 'Manda una muestra de los 5 emails branded al destino indicado';

    public function handle(): int
    {
        $to = (string) $this->option('to');
        $this->info("Enviando muestras a: {$to}");

        $supplier = Supplier::query()->first();
        if (! $supplier) {
            $this->error('No hay suppliers en la BD. Crea uno antes de correr este comando.');

            return self::FAILURE;
        }

        $document = SupplierDocument::query()->with(['documentType', 'documentState'])->first();
        $invitation = SupplierInvitation::query()->where('supplier_id', $supplier->id)->first()
            ?? SupplierInvitation::query()->first();
        $branchRequest = BranchRequest::query()->with(['branch'])->first();

        $results = [];

        $results[] = $this->send('supplier-invitation', function () use ($supplier, $invitation, $to): void {
            if (! $invitation) {
                throw new \RuntimeException('No hay SupplierInvitation en la BD.');
            }
            Mail::to($to)->send(new SupplierInvitationMailable($supplier, $invitation));
        });

        $results[] = $this->send('supplier-password-reset', function () use ($supplier, $to): void {
            Mail::to($to)->send(new SupplierPasswordResetMailable($supplier, 'sample-token-12345'));
        });

        $results[] = $this->send('supplier-documents-assigned', function () use ($supplier, $to): void {
            Mail::to($to)->send(new SupplierDocumentsAssignedMailable($supplier, 3));
        });

        $results[] = $this->send('supplier-document-status (Aprobado)', function () use ($supplier, $document, $to): void {
            if (! $document) {
                throw new \RuntimeException('No hay SupplierDocument en la BD.');
            }
            $approved = DocumentState::find(DocumentState::APROBADO);
            Mail::to($to)->send(new SupplierDocumentStatusMailable($supplier, $document, $approved));
        });

        $results[] = $this->send('supplier-document-status (Rechazado)', function () use ($supplier, $document, $to): void {
            if (! $document) {
                throw new \RuntimeException('No hay SupplierDocument en la BD.');
            }
            $document->notas = 'El archivo no coincide con el tipo de documento solicitado. Vuelve a subirlo escaneado y legible.';
            $rejected = DocumentState::find(DocumentState::RECHAZADO);
            Mail::to($to)->send(new SupplierDocumentStatusMailable($supplier, $document, $rejected));
        });

        $results[] = $this->send('branch-request-resolved', function () use ($branchRequest, $to): void {
            if (! $branchRequest) {
                throw new \RuntimeException('No hay BranchRequest en la BD.');
            }
            Mail::to($to)->send(new BranchRequestResolvedMailable($branchRequest));
        });

        $okCount = count(array_filter($results));
        $totalCount = count($results);

        $this->newLine();
        $this->info("Listo: {$okCount}/{$totalCount} correos enviados a {$to}");

        return $okCount === $totalCount ? self::SUCCESS : self::FAILURE;
    }

    private function send(string $label, callable $send): bool
    {
        try {
            $send();
            $this->line("  <fg=green>✓</> {$label}");

            return true;
        } catch (Throwable $e) {
            $this->line("  <fg=red>✗</> {$label} — ".$e->getMessage());

            return false;
        }
    }
}
