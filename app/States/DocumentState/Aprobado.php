<?php

namespace App\States\DocumentState;

use App\States\DocumentState;
use Filament\Support\Colors\Color;

class Aprobado extends DocumentState
{
    public static function getLabel(): string
    {
        return 'Aprobado';
    }

    public static function getColor(): string|Color
    {
        return Color::Green;
    }

    public static function isDefaultState(): bool
    {
        return false;
    }

    public static function isCompletedState(): bool
    {
        return true;
    }

    public function canTransitionTo(string $state): bool
    {
        return $state === Rechazado::class;
    }
}
