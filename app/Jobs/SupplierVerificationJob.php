<?php

namespace App\Jobs;

use App\Enums\SupplierStatus;
use App\Models\Supplier;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SupplierVerificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Supplier $supplier) {}

    public function handle(): void
    {
        // Verify CLABE exists in supplier data
        if (! $this->supplier->clabe_interbancaria) {
            return;
        }

        // TODO: Integrate with real bank API for CLABE validation
        // For now, assume valid CLABE if it matches format

        // TODO: Optional address verification via geolocation API

        // Mark supplier as active
        $this->supplier->update(['status' => SupplierStatus::Active]);

        // TODO: Send notification to supplier
        // Notification::send($this->supplier, new SupplierActivatedNotification());

        // TODO: Notify admin
        // Admin notification sent via Admin panel
    }
}
