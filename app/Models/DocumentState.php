<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentState extends Model
{
    /** @use HasFactory<\Database\Factories\DocumentStateFactory> */
    use HasFactory;

    protected $fillable = [
        'nombre',
        'etiqueta',
        'color',
        'icono',
        'por_defecto',
        'completado',
        'transiciones_permitidas',
    ];

    protected function casts(): array
    {
        return [
            'por_defecto' => 'boolean',
            'completado' => 'boolean',
            'transiciones_permitidas' => 'array',
        ];
    }
}
