<?php

namespace App\States;

use A909M\FilamentStateFusion\Concerns\StateFusionInfo;
use A909M\FilamentStateFusion\Contracts\HasFilamentStateFusion;
use Spatie\ModelStates\State;

abstract class DocumentState extends State implements HasFilamentStateFusion
{
    use StateFusionInfo;

    public static function getStateNames(): array
    {
        return [
            'pendiente' => DocumentState\Pendiente::class,
            'en_revision' => DocumentState\EnRevision::class,
            'aprobado' => DocumentState\Aprobado::class,
            'rechazado' => DocumentState\Rechazado::class,
        ];
    }
}
