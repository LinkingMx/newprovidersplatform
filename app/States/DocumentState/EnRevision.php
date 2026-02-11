<?php

namespace App\States\DocumentState;

use App\States\DocumentState;
use Filament\Support\Colors\Color;

class EnRevision extends DocumentState
{
    public static function getLabel(): string
    {
        return 'En Revisión';
    }

    public static function getColor(): string|Color
    {
        return Color::Blue;
    }

    public static function isDefaultState(): bool
    {
        return false;
    }

    public static function isCompletedState(): bool
    {
        return false;
    }

    public function canTransitionTo(string $state): bool
    {
        return in_array($state, [Aprobado::class, Rechazado::class]);
    }
}
