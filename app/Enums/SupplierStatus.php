<?php

declare(strict_types=1);

namespace App\Enums;

enum SupplierStatus: string
{
    case Created = 'created';
    case Invited = 'invited';
    case Registered = 'registered';
    case ProfileCompleted = 'profile_completed';
    case Active = 'active';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Creado',
            self::Invited => 'Invitado',
            self::Registered => 'Registrado',
            self::ProfileCompleted => 'Perfil Completo',
            self::Active => 'Activo',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Created => 'gray',
            self::Invited => 'yellow',
            self::Registered => 'blue',
            self::ProfileCompleted => 'orange',
            self::Active => 'green',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }
}
