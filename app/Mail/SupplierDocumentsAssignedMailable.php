<?php

namespace App\Mail;

use App\Models\Supplier;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SupplierDocumentsAssignedMailable extends Mailable
{
    public function __construct(
        private readonly Supplier $supplier,
        private readonly int $documentCount,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Tienes documentos pendientes por subir',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.supplier-documents-assigned',
            with: [
                'supplier' => $this->supplier,
                'documentCount' => $this->documentCount,
                'dashboardUrl' => route('dashboard'),
            ],
        );
    }
}
