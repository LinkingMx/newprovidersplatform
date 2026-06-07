<?php

namespace App\Models;

use App\Traits\HasActivityLogDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentType extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentTypeFactory> */
    use HasActivityLogDefaults, HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
        'validez_dias',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function providerTypes(): BelongsToMany
    {
        return $this->belongsToMany(ProviderType::class, 'document_type_provider_type')
            ->withPivot('obligatorio')
            ->withTimestamps();
    }

    public function supplierDocuments(): HasMany
    {
        return $this->hasMany(SupplierDocument::class);
    }
}
