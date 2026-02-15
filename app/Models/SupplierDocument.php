<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierDocument extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierDocumentFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'document_type_id',
        'document_state_id',
        'archivo_path',
        'archivo_nombre',
        'fecha_vencimiento',
        'notas',
        'uploaded_at',
        'reviewed_at',
        'reviewed_by',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'date',
            'uploaded_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function documentState(): BelongsTo
    {
        return $this->belongsTo(DocumentState::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function canUpload(): bool
    {
        return in_array($this->document_state_id, DocumentState::UPLOADABLE_STATES);
    }
}
