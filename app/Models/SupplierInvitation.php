<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierInvitation extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierInvitationFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'supplier_id',
        'token',
        'sent_at',
        'accepted_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'accepted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }
}
