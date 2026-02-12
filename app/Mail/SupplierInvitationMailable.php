<?php

namespace App\Mail;

use App\Models\Supplier;
use App\Models\SupplierInvitation;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SupplierInvitationMailable extends Mailable
{
    public function __construct(
        private readonly Supplier $supplier,
        private readonly SupplierInvitation $invitation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Invitación para completar tu perfil de proveedor',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.supplier-invitation',
            with: [
                'supplier' => $this->supplier,
                'invitation' => $this->invitation,
                'invitationUrl' => $this->buildInvitationUrl(),
            ],
        );
    }

    private function buildInvitationUrl(): string
    {
        $token = $this->invitation->token;
        return route('supplier.set-password', ['token' => $token]);
    }
}
