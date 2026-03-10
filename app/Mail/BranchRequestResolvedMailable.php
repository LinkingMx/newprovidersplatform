<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\BranchRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BranchRequestResolvedMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        private readonly BranchRequest $branchRequest,
    ) {}

    public function envelope(): Envelope
    {
        $status = $this->branchRequest->isApproved() ? 'aprobada' : 'rechazada';

        return new Envelope(
            from: new Address(config('mail.from.address'), config('mail.from.name')),
            subject: "Tu solicitud de sucursal fue {$status}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.branch-request-resolved',
            with: [
                'branchRequest' => $this->branchRequest,
                'supplier' => $this->branchRequest->supplier,
                'branch' => $this->branchRequest->branch,
                'dashboardUrl' => route('dashboard'),
            ],
        );
    }
}
