<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'status',
        'password_hash',
        'password_reset_token',
        'password_reset_expires_at',
        'address_street',
        'address_number',
        'address_neighborhood',
        'address_city',
        'address_country',
        'address_zip',
        'clabe_interbancaria',
    ];

    protected function casts(): array
    {
        return [
            'password_reset_expires_at' => 'datetime',
        ];
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'supplier_branches')
            ->withPivot('assigned_at', 'assigned_by')
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(SupplierInvitation::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPending(): bool
    {
        return in_array($this->status, ['created', 'invited', 'registered']);
    }
}
