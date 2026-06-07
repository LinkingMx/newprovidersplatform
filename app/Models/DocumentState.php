<?php

namespace App\Models;

use App\Traits\HasActivityLogDefaults;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentState extends Model
{
    use HasActivityLogDefaults, HasFactory;

    public const PENDIENTE = 1;

    public const EN_REVISION = 2;

    public const APROBADO = 3;

    public const RECHAZADO = 4;

    /** @var int[] */
    public const UPLOADABLE_STATES = [self::PENDIENTE, self::EN_REVISION, self::RECHAZADO];

    /** @var int[] */
    public const DELETABLE_STATES = [self::PENDIENTE, self::EN_REVISION];

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
