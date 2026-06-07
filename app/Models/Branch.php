<?php

namespace App\Models;

use App\Traits\HasActivityLogDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    /** @use HasFactory<\Database\Factories\BranchFactory> */
    use HasActivityLogDefaults, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'sap_db',
        'sap_bplid',
    ];

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'supplier_branches')
            ->withPivot('assigned_at', 'assigned_by')
            ->withTimestamps();
    }
}
