<?php

namespace App\Mail;

use App\Models\Supplier;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class SupplierPasswordResetMailable extends Mailable
{
    public function __construct(
        private readonly Supplier $supplier,
        private readonly string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: 'Restablece tu contraseña',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.supplier-password-reset',
            with: [
                'supplier' => $this->supplier,
                'resetUrl' => $this->buildResetUrl(),
            ],
        );
    }

    private function buildResetUrl(): string
    {
        return route('supplier.reset-password', [
            'token' => $this->token,
            'email' => $this->supplier->email,
        ]);
    }
}
