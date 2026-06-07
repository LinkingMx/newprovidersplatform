<?php

namespace App\Models;

use App\Enums\SupplierStatus;
use App\Traits\HasActivityLogDefaults;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model implements Authenticatable
{
    /** @use HasFactory<\Database\Factories\SupplierFactory> */
    use HasActivityLogDefaults, HasFactory, SoftDeletes;

    protected $hidden = [
        'password_hash',
        'password_reset_token',
        'password_reset_expires_at',
    ];

    protected $fillable = [
        'name',
        'email',
        'rfc',
        'status',
        'provider_type_id',
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
            'status' => SupplierStatus::class,
            'password_reset_expires_at' => 'datetime',
        ];
    }

    public function providerType(): BelongsTo
    {
        return $this->belongsTo(ProviderType::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SupplierDocument::class);
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

    public function branchRequests(): HasMany
    {
        return $this->hasMany(BranchRequest::class);
    }

    public function isActive(): bool
    {
        return $this->status === SupplierStatus::Active;
    }

    public function canLogin(): bool
    {
        return in_array($this->status, [
            SupplierStatus::Registered,
            SupplierStatus::ProfileCompleted,
            SupplierStatus::Active,
        ]);
    }

    public function isPending(): bool
    {
        return in_array($this->status, [
            SupplierStatus::Created,
            SupplierStatus::Invited,
            SupplierStatus::Registered,
        ]);
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void {}

    public function getRememberTokenName(): string
    {
        return '';
    }
}
