<?php

namespace App\States\DocumentState;

use App\States\DocumentState;
use Filament\Support\Colors\Color;

class Rechazado extends DocumentState
{
    public static function getLabel(): string
    {
        return 'Rechazado';
    }

    public static function getColor(): string|Color
    {
        return Color::Red;
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
        return $state === EnRevision::class;
    }
}
