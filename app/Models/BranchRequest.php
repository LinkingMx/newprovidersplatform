<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BranchRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BranchRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'branch_id',
        'status',
        'notas_proveedor',
        'notas_admin',
        'requested_at',
        'resolved_at',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => BranchRequestStatus::class,
            'requested_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', BranchRequestStatus::Pending);
    }

    public function isPending(): bool
    {
        return $this->status === BranchRequestStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === BranchRequestStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->status === BranchRequestStatus::Rejected;
    }
}
