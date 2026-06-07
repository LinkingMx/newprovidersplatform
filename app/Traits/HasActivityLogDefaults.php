<?php

declare(strict_types=1);

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Trait estándar para auditoría con spatie/laravel-activitylog.
 *
 * - Registra todos los campos del $fillable del modelo.
 * - Excluye campos sensibles (passwords, tokens, 2FA).
 * - Sólo loguea cambios reales (logOnlyDirty).
 * - No genera entries vacíos.
 */
trait HasActivityLogDefaults
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logExcept([
                'password',
                'password_hash',
                'password_reset_token',
                'password_reset_expires_at',
                'remember_token',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'two_factor_confirmed_at',
                'api_token',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
