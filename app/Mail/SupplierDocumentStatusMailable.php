<?php

namespace App\Mail;

use App\Models\DocumentState;
use App\Models\Supplier;
use App\Models\SupplierDocument;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SupplierDocumentStatusMailable extends Mailable
{
    public function __construct(
        private readonly Supplier $supplier,
        private readonly SupplierDocument $document,
        private readonly DocumentState $newState,
    ) {}

    public function envelope(): Envelope
    {
        $documentName = $this->document->documentType->nombre;
        $stateName = $this->newState->etiqueta;

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: "Tu documento \"{$documentName}\" fue {$stateName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.supplier-document-status',
            with: [
                'supplier' => $this->supplier,
                'document' => $this->document,
                'state' => $this->newState,
                'dashboardUrl' => route('dashboard'),
            ],
        );
    }
}
