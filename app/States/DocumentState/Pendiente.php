<?php

namespace App\States\DocumentState;

use App\States\DocumentState;
use Filament\Support\Colors\Color;

class Pendiente extends DocumentState
{
    public static function getLabel(): string
    {
        return 'Pendiente';
    }

    public static function getColor(): string|Color
    {
        return Color::Gray;
    }

    public static function isDefaultState(): bool
    {
        return true;
    }

    public static function isCompletedState(): bool
    {
        return false;
    }

    public function canTransitionTo($newState, ...$transitionArgs): bool
    {
        return $newState === EnRevision::class;
    }
}
