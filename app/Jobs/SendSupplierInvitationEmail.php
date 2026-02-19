<?php

namespace App\Jobs;

use App\Mail\SupplierInvitationMailable;
use App\Models\Supplier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendSupplierInvitationEmail implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Supplier $supplier,
    ) {}

    public function handle(): void
    {
        $invitation = $this->supplier->invitations()->latest('id')->first();

        if (! $invitation) {
            return;
        }

        Mail::to($this->supplier->email)->send(
            new SupplierInvitationMailable($this->supplier, $invitation)
        );

        $invitation->update(['sent_at' => now()]);
    }
}
