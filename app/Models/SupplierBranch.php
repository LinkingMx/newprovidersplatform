<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierBranch extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierBranchFactory> */
    use HasFactory;

    protected $table = 'supplier_branches';

    protected $fillable = [
        'supplier_id',
        'branch_id',
        'assigned_at',
        'assigned_by',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
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
}
